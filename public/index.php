<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// إصلاح فوري لـ APP_KEY
if (empty($_ENV['APP_KEY']) || $_ENV['APP_KEY'] === 'base64:GENERATED_KEY_WILL_BE_SET_AUTOMATICALLY') {
    $_ENV['APP_KEY'] = 'base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=';
    putenv('APP_KEY=base64:1jJ7lx/yprzypdaIzSD6nk1GaImlQuKx4QE2+TqQT2Q=');
}

// إصلاح إعدادات قاعدة البيانات
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = '/app/database/database.sqlite';
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=/app/database/database.sqlite');

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
