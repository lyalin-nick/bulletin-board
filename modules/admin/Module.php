<?php

namespace app\modules\admin;

use app\models\Staff;
use Yii;
use yii\web\Cookie;
use yii\web\User;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        Yii::$app->set('user', [
            'class' => User::class,
            'identityClass' => Staff::class,
            'enableSession' => true,
            'enableAutoLogin' => false,
            'authTimeout' => 3600 * 4, // авто-логаут по бездействию
            'identityCookie' => [
                'name' => '_identity-admin',
                'httpOnly' => true,
                'secure' => !YII_ENV_DEV,
                'sameSite' => Cookie::SAME_SITE_LAX,
            ],
            'idParam' => '__id-admin',
            'authTimeoutParam' => '__expire-admin',
            'returnUrlParam' => '__returnUrl-admin',
            'loginUrl' => ['/admin/auth/login'],
        ]);
    }
}
