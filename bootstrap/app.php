<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // AJOUT : enregistrement du middleware Administrateur
        $middleware->alias([
            'Administrateur' => \App\Http\Middleware\AdministrateurMiddleware::class,
        ]);

        // AJOUT : force le changement de mot de passe temporaire sur tout le site
        $middleware->appendToGroup('web', \App\Http\Middleware\ForcerChangementMotDePasse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();