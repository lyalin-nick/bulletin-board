<?php

declare(strict_types=1);

use app\helpers\Env;

return [
    'class' => \yii\db\Connection::class,
    'dsn' => sprintf(
        'mysql:host=%s;port=%d;dbname=%s',
        Env::required('DB_HOST'),
        Env::int('DB_PORT', 3306),
        Env::required('DB_NAME'),
    ),
    'username' => Env::required('DB_USER'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
