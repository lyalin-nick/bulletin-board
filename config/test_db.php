<?php

declare(strict_types=1);

use app\helpers\Env;

$db = require __DIR__ . '/db.php';

$testName = Env::required('DB_TEST_NAME');

if ($testName === Env::required('DB_NAME')) {
    throw new RuntimeException('DB_TEST_NAME совпадает с DB_NAME — тесты снесут рабочие данные');
}

$db['dsn'] = sprintf(
    'mysql:host=%s;port=%d;dbname=%s',
    Env::required('DB_HOST'),
    Env::int('DB_PORT', 3306),
    $testName,
);

return $db;
