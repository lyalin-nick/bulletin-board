<?php

namespace app\forms\auth;

use app\forms\BaseForm;

class RefreshTokenForm extends BaseForm
{
    public string $refresh_token = '';

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            ['refresh_token', 'required'],
            ['refresh_token', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'refresh_token' => 'Refresh-токен'
        ];
    }
}
