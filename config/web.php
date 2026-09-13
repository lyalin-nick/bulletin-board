<?php

declare(strict_types=1);

use app\helpers\Env;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'UTC',
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'viewPath' => '@app/mail',
                'transport' => [
                    'scheme' => Env::get('MAILER_ENCRYPTION') ? 'smtps' : 'smtp',
                    'host' => Env::required('MAILER_HOST'),
                    'port' => Env::int('MAILER_PORT', 1025),
                    'username' => Env::get('MAILER_USER', ''),
                    'password' => Env::get('MAILER_PASSWORD', ''),
                ],
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => ['class' => 'app\modules\admin\Module'],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => Env::required('YII_COOKIE_VALIDATION_KEY'),
            'trustedHosts' => ['172.16.0.0/12'],
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableSession' => false,
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'class' => \app\components\ApiErrorHandler::class,
            'errorAction' => 'admin/error/index',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                [
                    'class' => \yii\web\GroupUrlRule::class,
                    'prefix' => 'api/v1',
                    'routePrefix' => '',
                    'rules' => [
                        'POST auth/login' => 'auth/login',
                        'POST auth/signup' => 'auth/signup',
                        'POST auth/logout' => 'auth/logout',
                        'POST auth/confirm' => 'auth/confirm',
                        'POST auth/resend-code' => 'auth/resend',
                        'POST auth/refresh' => 'auth/refresh',
                    ],
                ],

                // модуль админки
                'admin' => 'admin/default/index',
                'admin/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'admin/<controller>/<action>',
                'admin/<controller:[\w-]+>/<action:[\w-]+>' => 'admin/<controller>/<action>',
                'admin/<controller:[\w-]+>' => 'admin/<controller>/index',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;
