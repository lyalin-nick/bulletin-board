<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

abstract class BaseAdminController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => $this->accessRules(),
            ],
        ]);
    }

    protected function accessRules(): array
    {
        return [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ];
    }
}
