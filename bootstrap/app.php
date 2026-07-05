<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\CheckDatabaseInstalled::class);
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\IsSuperAdmin::class,
            'operator' => \App\Http\Middleware\IsOperator::class,
            'guru' => \App\Http\Middleware\IsGuru::class,
            'kepsek' => \App\Http\Middleware\IsKepsek::class,
            'siswa' => \App\Http\Middleware\IsSiswa::class,
        ]);

        
        
        
        $middleware->validateCsrfTokens(except: [
            'api/mobile/*',
            'api/exam/student',
            'operator/api/asesmen',
            'install/run',
        ]);

        
        
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenance::class,
            \App\Http\Middleware\CheckIpBlock::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\LogActivity::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\LogActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
    })->create();



$app->usePublicPath(dirname($app->basePath()));

return $app;
