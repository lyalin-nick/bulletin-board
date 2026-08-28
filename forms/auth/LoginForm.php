<?php

declare(strict_types=1);

namespace app\forms\auth;

use app\forms\BaseForm;
use app\helpers\Phone;
use app\models\User;
use Yii;

/**
 * @property-read User|null $user
 */
class LoginForm extends BaseForm
{
    public string $login = '';
    public string $password = '';

    private User|null $_user = null;
    private bool $_userLoaded = false;

    public function rules(): array
    {
        return [
            [['login', 'password'], 'required'],
            [['login', 'password'], 'string', 'max' => 255],
            ['login', 'trim'],
            ['login', 'validateLogin'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Определяем, что передано — email или телефон, и приводим номер к E.164.
     * @param string $attribute
     * @param array|null $params
     * @return void
     */
    public function validateLogin(string $attribute, array|null $params): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (str_contains($this->login, '@')) {
            if (!filter_var($this->login, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, 'Некорректный e-mail.');

                return;
            }

            $this->login = mb_strtolower($this->login);

            return;
        }

        if (!Phone::looksLikePhone($this->login)) {
            $this->addError($attribute, 'Укажите e-mail или номер телефона.');

            return;
        }

        $phone = Phone::normalize($this->login);

        if ($phone === null) {
            $this->addError($attribute, 'Некорректный номер телефона.');

            return;
        }

        $this->login = $phone;
    }

    public function validatePassword(string $attribute, array|null $params): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if ($user === null || !Yii::$app->security->validatePassword($this->password, $user->password_hash)) {
            $this->addError($attribute, 'Неверный логин или пароль.');
        }
    }

    public function getUser(): User|null
    {
        if (!$this->_userLoaded) {
            $this->_user = User::findByLogin($this->login);
            $this->_userLoaded = true;
        }

        return $this->_user;
    }

    public function attributeLabels(): array
    {
        return [
            'login' => 'Email или телефон',
            'password' => 'Пароль',
        ];
    }
}
