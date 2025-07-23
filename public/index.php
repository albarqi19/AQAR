<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// إصلاح فوري لـ APP_KEY
if (empty($_ENV['APP_KEY']) || $_ENV['APP_KEY'] === 'base64:GENERATED_KEY_WILL_BE_SET_AUTOMATICALLY') {
    $_ENV['APP_KEY'] = 'base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=';
    putenv('APP_KEY=base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=');
}

// إصلاح إعدادات قاعدة البيانات MySQL
$_ENV['DB_CONNECTION'] = 'mysql';
$_ENV['DB_HOST'] = $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_PORT'] = $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? '3306';
$_ENV['DB_DATABASE'] = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_DATABASE'] ?? 'property_management';
$_ENV['DB_USERNAME'] = $_ENV['MYSQLUSER'] ?? $_ENV['DB_USERNAME'] ?? 'root';
$_ENV['DB_PASSWORD'] = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? '';

putenv('DB_CONNECTION=mysql');
putenv('DB_HOST=' . $_ENV['DB_HOST']);
putenv('DB_PORT=' . $_ENV['DB_PORT']);
putenv('DB_DATABASE=' . $_ENV['DB_DATABASE']);
putenv('DB_USERNAME=' . $_ENV['DB_USERNAME']);
putenv('DB_PASSWORD=' . $_ENV['DB_PASSWORD']);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
