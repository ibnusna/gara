<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));


if (file_exists($maintenance = __DIR__.'/gara/storage/framework/maintenance.php')) {
    require $maintenance;
}


require __DIR__.'/gara/vendor/autoload.php';



$app = require_once __DIR__.'/gara/bootstrap/app.php';



$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
