<?php

declare(strict_types=1);

namespace app\services;

use app\dto\DeviceDto;
use app\dto\RegisterDto;
use app\models\User;
use app\models\user\AccessToken;
use app\services\exceptions\InactiveUserException;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\Exception;

class AuthService
{
    /**
     * Создание токенов доступа для авторизации
     * @param User $user
     * @param DeviceDto $device
     * @return array{token: string, token_expired_at: string, refresh_token: string, refresh_token_expired_at: string}
     * @throws Exception
     * @throws Throwable
     */
    public function login(User $user, DeviceDto $device): array
    {
        if ($user->status !== User::STATUS_ACTIVE) {
            throw new InactiveUserException('Учётная запись не активна.');
        }

        return $this->issueTokenPair($user, $device);
    }

    /**
     * Отзыв токена доступа
     * @param AccessToken $accessToken
     * @return void
     */
    public function logout(AccessToken $accessToken): void
    {
        $accessToken->updateAttributes(['revoked_at' => date('Y-m-d H:i:s')]);
    }

    public function logoutAll(User $user): int
    {
        return AccessToken::revokeAllForUser($user->id);
    }

    /**
     * Выпуск нового токена и отзыв старых(при наличии для текущего девайса)
     * @param User $user
     * @param DeviceDto $device
     * @return array
     * @throws Throwable
     * @throws Exception
     */
    private function issueTokenPair(User $user, DeviceDto $device): array
    {
        $token = Yii::$app->security->generateRandomString();
        $refreshToken = Yii::$app->security->generateRandomString();
        $now = time();

        return Yii::$app->db->transaction(function () use ($user, $device, $token, $refreshToken, $now): array {
            AccessToken::revokeForDevice($user->id, $device->userAgent);

            $accessToken = new AccessToken([
                'user_id' => $user->id,
                'token' => AccessToken::hashToken($token),
                'token_expired_at' => date('Y-m-d H:i:s', $now + AccessToken::TOKEN_EXPIRE_TIME),
                'refresh_token' => AccessToken::hashToken($refreshToken),
                'refresh_token_expired_at' => date('Y-m-d H:i:s', $now + AccessToken::REFRESH_TOKEN_EXPIRE_TIME),
                'device_name' => $device->deviceName,
                'user_agent' => $device->userAgent,
                'ip' => $device->ip,
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
        });
    }
}
