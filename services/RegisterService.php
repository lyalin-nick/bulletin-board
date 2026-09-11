<?php

declare(strict_types=1);

namespace app\services;

use app\dto\RegisterDto;
use app\models\User;
use app\models\user\ConfirmCode;
use app\services\exceptions\ConfirmCodeException;
use app\services\exceptions\EmailAlreadyTakenException;
use app\services\exceptions\RegistrationFailedException;
use Throwable;
use Yii;
use yii\db\IntegrityException;

class RegisterService
{
    /**
     * Возвращает созданного пользователя
     *
     * @throws EmailAlreadyTakenException email заняли между валидацией формы и вставкой
     * @throws RegistrationFailedException не удалось сохранить пользователя или код
     */
    public function createUser(RegisterDto $dto): User
    {
        $code = (string) random_int(100000, 999999);

        try {
            $user = Yii::$app->db->transaction(function () use ($dto, $code): User {
                $user = new User([
                    'name' => $dto->name,
                    'surname' => $dto->surname,
                    'email' => $dto->email,
                ]);

                $user->setPassword($dto->password);
                $user->generateAuthKey();

                if (!$user->save()) {
                    Yii::error([
                        'message' => 'Не удалось сохранить User',
                        'email' => $dto->email,
                        'errors' => $user->getErrors(),
                    ], __METHOD__);

                    if ($user->hasErrors('email') && User::find()->where(['email' => $dto->email])->exists()) {
                        throw new EmailAlreadyTakenException('Этот e-mail уже зарегистрирован.');
                    }

                    throw new RegistrationFailedException('Не удалось создать пользователя.');
                }

                ConfirmCode::expirePrevious($user->id, ConfirmCode::PURPOSE_SIGNUP);

                $confirmCode = new ConfirmCode([
                    'user_id' => $user->id,
                    'channel' => ConfirmCode::CHANNEL_EMAIL,
                    'purpose' => ConfirmCode::PURPOSE_SIGNUP,
                    'target' => $dto->email,
                    'code_hash' => hash('sha256', $code),
                ]);
                $confirmCode->setExpires();

                if (!$confirmCode->save()) {
                    Yii::error([
                        'message' => 'Не удалось сохранить ConfirmCode',
                        'userId' => $user->id,
                        'errors' => $confirmCode->getErrors(),
                    ], __METHOD__);

                    throw new RegistrationFailedException('Не удалось создать код подтверждения.');
                }

                $user->refresh();

                return $user;
            });
        } catch (IntegrityException $e) {
            throw new EmailAlreadyTakenException('Этот e-mail уже зарегистрирован.', 0, $e);
        }

        $this->sendConfirmationCode($user, $code);

        return $user;
    }

    /**
     * Отправка кода подтверждения
     * @param User $user
     * @param string $code
     * @return void
     */
    private function sendConfirmationCode(User $user, string $code): void
    {
        try {
            $isSent = Yii::$app->mailer
                ->compose(
                    ['html' => 'confirm-code'],
                    ['user' => $user, 'purpose' => ConfirmCode::PURPOSE_SIGNUP, 'code' => $code],
                )
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($user->email)
                ->setSubject('Код подтверждения')
                ->send();
        } catch (Throwable $e) {
            Yii::error([
                'message' => 'Исключение при отправке кода подтверждения',
                'userId' => $user->id,
                'error' => $e->getMessage(),
            ], __METHOD__);

            return;
        }

        if (!$isSent) {
            Yii::warning([
                'message' => 'Код подтверждения не отправлен',
                'userId' => $user->id,
            ], __METHOD__);
        }
    }

    /**
     * Подтверждение почты по коду из письма
     *
     * @param string $email
     * @param string $code
     * @return User
     * @throws ConfirmCodeException код не найден, просрочен, исчерпан или не совпал
     * @throws RegistrationFailedException не удалось зафиксировать подтверждение
     */
    public function confirmUser(string $email, string $code): User
    {
        $user = User::findOne(['email' => $email]);

        if ($user === null) {
            throw new ConfirmCodeException('Код не найден. Запросите новый код подтверждения.');
        }

        if ($user->isActive() && $user->email_verified_at !== null) {
            throw new ConfirmCodeException('Учётная запись уже подтверждена — войдите с паролем.');
        }

        $confirmCode = ConfirmCode::find()
            ->where([
                'user_id' => $user->id,
                'purpose' => ConfirmCode::PURPOSE_SIGNUP,
                'channel' => ConfirmCode::CHANNEL_EMAIL,
                'confirmed_at' => null,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($confirmCode === null) {
            throw new ConfirmCodeException('Код не найден. Запросите новый код подтверждения.');
        }

        if ($confirmCode->expires_at <= date('Y-m-d H:i:s')) {
            throw new ConfirmCodeException('Код просрочен. Запросите новый код подтверждения.');
        }

        if ($confirmCode->checkLimit()) {
            throw new ConfirmCodeException('Превышено число попыток. Запросите новый код подтверждения.');
        }

        $confirmCode->increaseAttempts();

        if (!hash_equals($confirmCode->code_hash, hash('sha256', $code))) {
            throw new ConfirmCodeException('Код подтверждения неверный.');
        }

        return Yii::$app->db->transaction(function () use ($confirmCode, $user): User {
            $confirmCode->confirmed_at = date('Y-m-d H:i:s');

            if (!$confirmCode->save()) {
                Yii::error([
                    'message' => 'Не удалось сохранить ConfirmCode при подтверждении',
                    'userId' => $user->id,
                    'errors' => $confirmCode->getErrors(),
                ], __METHOD__);

                throw new RegistrationFailedException('Не удалось подтвердить учётную запись.');
            }

            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->status = User::STATUS_ACTIVE;

            if (!$user->save()) {
                Yii::error([
                    'message' => 'Не удалось сохранить User при подтверждении',
                    'userId' => $user->id,
                    'errors' => $user->getErrors(),
                ], __METHOD__);

                throw new RegistrationFailedException('Не удалось подтвердить учётную запись.');
            }

            return $user;
        });
    }

    /**
     * Повторная отправка кода подтверждения регистрации.
     * Прошлые коды гасятся: действителен только последний отправленный.
     *
     * @throws ConfirmCodeException адрес не найден, уже подтверждён или код запрошен слишком часто
     * @throws RegistrationFailedException не удалось создать код
     */
    public function resendConfirmationCode(string $email): void
    {
        $user = User::findOne(['email' => $email]);

        if ($user === null) {
            throw new ConfirmCodeException('Пользователь с таким email не найден.');
        }

        if ($user->isActive() && $user->email_verified_at !== null) {
            throw new ConfirmCodeException('Учётная запись уже подтверждена — войдите с паролем.');
        }

        $this->assertResendAllowed($user);

        ConfirmCode::expirePrevious($user->id, ConfirmCode::PURPOSE_SIGNUP);

        $code = (string) random_int(100000, 999999);

        $confirmCode = new ConfirmCode([
            'user_id' => $user->id,
            'channel' => ConfirmCode::CHANNEL_EMAIL,
            'purpose' => ConfirmCode::PURPOSE_SIGNUP,
            'target' => $user->email,
            'code_hash' => hash('sha256', $code),
        ]);
        $confirmCode->setExpires();

        if (!$confirmCode->save()) {
            Yii::error([
                'message' => 'Не удалось сохранить ConfirmCode при повторной отправке',
                'userId' => $user->id,
                'errors' => $confirmCode->getErrors(),
            ], __METHOD__);

            throw new RegistrationFailedException('Не удалось создать код подтверждения.');
        }

        $this->sendConfirmationCode($user, $code);
    }

    /**
     * Дешёвый тормоз до появления rate limit в Redis (этап 3): без него
     * эндпоинт позволяет заваливать чужой ящик письмами без ограничений.
     * Смотрим на created_at последнего кода — expirePrevious его не меняет,
     * так что окно держится и после гашения.
     */
    private function assertResendAllowed(User $user): void
    {
        $lastCode = ConfirmCode::find()
            ->where(['user_id' => $user->id, 'purpose' => ConfirmCode::PURPOSE_SIGNUP])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastCode === null) {
            return;
        }

        $interval = Yii::$app->params['confirmCodeResendInterval'] ?? 60;
        $availableAt = strtotime($lastCode->created_at) + $interval;

        if ($availableAt > time()) {
            throw new ConfirmCodeException(
                sprintf('Код уже отправлен. Повторная отправка возможна через %d сек.', $availableAt - time()),
            );
        }
    }
}
