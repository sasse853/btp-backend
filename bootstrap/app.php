<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias du middleware de controle de role : role:admin, role:chef_chantier
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Sanctum pour les requetes stateful du SPA (cookies)
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Reponses JSON homogenes pour l'API : {success, message, data}
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $payload = fn (string $message, int $code, $errors = null) => response()->json(array_filter([
                'success' => false,
                'message' => $message,
                'errors'  => $errors,
            ], fn ($v) => ! is_null($v)), $code);

            return match (true) {
                $e instanceof ValidationException     => $payload('Les donnees fournies sont invalides.', 422, $e->errors()),
                $e instanceof AuthenticationException => $payload('Non authentifie.', 401),
                $e instanceof AuthorizationException  => $payload("Action non autorisee.", 403),
                $e instanceof ModelNotFoundException  => $payload('Ressource introuvable.', 404),
                $e instanceof NotFoundHttpException   => $payload('Ressource introuvable.', 404),
                $e instanceof HttpExceptionInterface  => $payload($e->getMessage() ?: 'Erreur.', $e->getStatusCode()),
                default                               => $payload(
                    config('app.debug') ? $e->getMessage() : 'Une erreur interne est survenue.',
                    500
                ),
            };
        });
    })->create();
