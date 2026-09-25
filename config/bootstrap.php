<?php

declare(strict_types=1);

use app\helpers\Env;
use Dotenv\Dotenv;
use yii\helpers\Inflector;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'])->notEmpty();

defined('YII_DEBUG') or define('YII_DEBUG', Env::bool('YII_DEBUG', false));
defined('YII_ENV') or define('YII_ENV', Env::get('YII_ENV', 'prod'));

Inflector::$transliterator = 'Russian-Latin/BGN; Any-Latin; Latin-ASCII; NFKD; [:nonspacing mark:] remove; Lower()';
