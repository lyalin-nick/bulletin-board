<?php

declare(strict_types=1);

namespace app\forms\auth;

use app\components\validators\PasswordValidator;
use app\forms\BaseForm;
use app\models\User;

class SignupForm extends BaseForm
{
    public string $name = '';
    public string $surname = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirm = '';

    public function rules(): array
    {
        return [
            [['name', 'email', 'password', 'password_confirm'], 'required'],
            [['name', 'surname'], 'trim'],
            [['name', 'surname'], 'string', 'max' => 255],

            ['email', 'filter', 'filter' => static fn(string $v): string => mb_strtolower(trim($v))],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],

            ['password', PasswordValidator::class, 'min' => 4, 'lower' => true, 'upper' => true],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'email' => 'Email',
            'password' => 'Пароль',
            'password_confirm' => 'Подтверждение пароля',
        ];
    }
}
