<?php

declare(strict_types=1);

namespace app\services;

use app\dto\RegisterDto;
use app\models\User;
use app\models\user\ConfirmCode;
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
}
