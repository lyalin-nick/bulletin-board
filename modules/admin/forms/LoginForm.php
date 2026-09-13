<?php

declare(strict_types=1);

namespace app\modules\admin\forms;

use app\models\Staff;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public string $email = '';
    public string $password = '';

    private Staff|null $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'filter', 'filter' => static fn(string $v): string => mb_strtolower(trim($v))],
            ['email', 'email'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword(string $attribute, array|null $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный email или пароль.');
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $staff = $this->getUser();

        if (!Yii::$app->user->login($staff)) {
            return false;
        }

        $staff->touchLastLogin();

        return true;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Staff|null
     */
    protected function getUser(): Staff|null
    {
        if ($this->_user === null) {
            $this->_user = Staff::findByEmail($this->email);
        }

        return $this->_user;
    }
}
