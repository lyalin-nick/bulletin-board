<?php

namespace app\forms\auth;

use app\forms\BaseForm;
use app\helpers\Phone;
use app\models\User;
use app\models\user\ConfirmCode;

class ResendCodeForm extends BaseForm
{
    public string $email = '';
    public string $phone = '';

    public function scenarios(): array
    {
        return [
            ConfirmCode::CHANNEL_EMAIL => ['email'],
            ConfirmCode::CHANNEL_PHONE => ['phone'],
            self::SCENARIO_DEFAULT => ['email'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'required'],
            ['email', 'filter', 'filter' => static fn(string $v): string => mb_strtolower(trim($v))],
            ['email', 'email'],
            ['email', 'validateEmail'],
            // при сценарии отправки на номер телефона
            ['phone', 'required'],
            ['phone', 'validatePhone'],
        ];
    }

    /**
     * @param $attribute
     * @return void
     */
    public function validateEmail($attribute): void
    {
        if (!User::find()->where(['email' => $this->$attribute])->exists()) {
            $this->addError('email', 'Пользователь с таким email не найден.');
        }
    }

    /**
     * @param $attribute
     * @param array|null $params
     * @return void
     */
    public function validatePhone($attribute, array|null $params): void
    {
        $phone = Phone::normalize($this->$attribute);

        if ($phone === null) {
            $this->addError($attribute, 'Некорректный номер телефона.');

            return;
        }

        $this->$attribute = $phone;

        if (!User::find()->where(['phone' => $this->$attribute])->exists()) {
            $this->addError($attribute, 'Пользователь с таким номером телефона не найден.');
        }
    }


    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'phone' => 'Номер телефона',
        ];
    }
}
