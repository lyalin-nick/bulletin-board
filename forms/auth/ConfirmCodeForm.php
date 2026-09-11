<?php

namespace app\forms\auth;

use app\forms\BaseForm;

class ConfirmCodeForm extends BaseForm
{
    public string $code = '';
    public string $email = '';

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['code', 'email'], 'required'],
            ['email', 'filter', 'filter' => static fn(string $v): string => mb_strtolower(trim($v))],
            ['email', 'email'],
            ['code', 'match', 'pattern' => '/^\d{6}$/', 'message' => 'Код должен содержать 6 цифр.'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'code' => 'Код подтверждения',
            'email' => 'Email',
        ];
    }
}
