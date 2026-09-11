<?php

declare(strict_types=1);

namespace app\services;

use app\dto\DeviceDto;
use app\models\User;
use app\models\user\AccessToken;
use app\services\exceptions\InactiveUserException;
use app\services\exceptions\InvalidRefreshTokenException;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\Exception;

class AuthService
{
    /**
     * Создание токенов доступа для авторизации
     * @return array{token: string, token_expired_at: string, refresh_token: string, refresh_token_expired_at: string}
     * @throws Exception
     * @throws Throwable
     */
    public function login(User $user, DeviceDto $device): array
    {
        if ($user->status !== User::STATUS_ACTIVE) {
            throw new InactiveUserException('Учётная запись не активна.');
        }

        return Yii::$app->db->transaction(function () use ($user, $device): array {
            AccessToken::revokeForDevice($user->id, $device->userAgent);

            return $this->createTokenPair($user, $device->deviceName, $device->userAgent, $device->ip);
        });
    }

    /**
     * Отзыв токена доступа
     */
    public function logout(AccessToken $accessToken): void
    {
        $accessToken->revoke();
    }

    public function logoutAll(User $user): int
    {
        return AccessToken::revokeAllForUser($user->id);
    }

    /**
     * Ротация пары по refresh-токену
     *
     * @return array{token: string, token_expired_at: string, refresh_token: string, refresh_token_expired_at: string}
     * @throws InvalidRefreshTokenException
     * @throws Throwable
     */
    public function refreshTokens(string $refreshToken): array
    {
        $accessToken = AccessToken::find()
            ->with('user')
            ->where(['refresh_token' => AccessToken::hashToken($refreshToken)])
            ->one();

        if ($accessToken === null) {
            throw new InvalidRefreshTokenException('Недействительный refresh-токен.');
        }

        if ($accessToken->isRevoked()) {
            Yii::warning([
                'message' => 'Повторное использование ротированного refresh-токена!!!',
                'userId' => $accessToken->user_id,
                'tokenId' => $accessToken->id,
                'ip' => Yii::$app->request->userIP ?? null,
            ], __METHOD__);

            AccessToken::revokeAllForUser($accessToken->user_id);

            throw new InvalidRefreshTokenException('Недействительный refresh-токен.');
        }

        if (strtotime($accessToken->refresh_token_expired_at) <= time()) {
            throw new InvalidRefreshTokenException('Срок действия сессии истёк — войдите с паролем.');
        }

        $user = $accessToken->user;

        if ($user === null || !$user->isActive()) {
            throw new InvalidRefreshTokenException('Недействительный refresh-токен.');
        }

        return Yii::$app->db->transaction(function () use ($accessToken, $user): array {
            $accessToken->revoke();

            return $this->createTokenPair(
                $user,
                $accessToken->device_name,
                $accessToken->user_agent,
                $accessToken->ip,
            );
        });
    }

    /**
     * Выпуск пары токенов
     *
     * @return array{token: string, token_expired_at: string, refresh_token: string, refresh_token_expired_at: string}
     * @throws Exception
     */
    private function createTokenPair(User $user, ?string $deviceName, ?string $userAgent, ?string $ip): array
    {
        $token = Yii::$app->security->generateRandomString();
        $refreshToken = Yii::$app->security->generateRandomString();
        $now = time();

        $accessToken = new AccessToken([
            'user_id' => $user->id,
            'token' => AccessToken::hashToken($token),
            'token_expired_at' => date('Y-m-d H:i:s', $now + AccessToken::TOKEN_EXPIRE_TIME),
            'refresh_token' => AccessToken::hashToken($refreshToken),
            'refresh_token_expired_at' => date('Y-m-d H:i:s', $now + AccessToken::REFRESH_TOKEN_EXPIRE_TIME),
            'device_name' => $deviceName,
            'user_agent' => $userAgent,
            'ip' => $ip,
            'last_used_at' => date('Y-m-d H:i:s', $now),
        ]);

        if (!$accessToken->save()) {
            Yii::error([
                'message' => 'Не удалось сохранить AccessToken',
                'userId' => $user->id,
                'errors' => $accessToken->getErrors(),
            ], __METHOD__);

            throw new RuntimeException('Не удалось выпустить токен доступа.');
        }

        return [
            'token' => $token,
            'token_expired_at' => $accessToken->token_expired_at,
            'refresh_token' => $refreshToken,
            'refresh_token_expired_at' => $accessToken->refresh_token_expired_at,
        ];
    }
}
