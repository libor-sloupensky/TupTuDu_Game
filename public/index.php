<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Detect Environment: Local vs Webglobe Server
|--------------------------------------------------------------------------
| On Webglobe: public files are in /_sub/game/, app files in /laravel-game/
| Locally: standard Laravel structure (public/../)
*/
$isWebglobe = file_exists(__DIR__.'/../../laravel-game/vendor/autoload.php');

if ($isWebglobe) {
    $basePath = __DIR__.'/../../laravel-game';
    $autoload = $basePath.'/vendor/autoload.php';
    $maintenance = $basePath.'/storage/framework/maintenance.php';
} else {
    $basePath = __DIR__.'/..';
    $autoload = $basePath.'/vendor/autoload.php';
    $maintenance = $basePath.'/storage/framework/maintenance.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance)) {
    require $maintenance;
}

// Register the Composer autoloader...
require $autoload;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
