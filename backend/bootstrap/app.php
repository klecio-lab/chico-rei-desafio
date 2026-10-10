<?php

use App\Models\Product;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sem token em rota protegida => 401 JSON, nunca redirect (regra 7 de autenticacao.md).
        Authenticate::redirectUsing(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 401 sempre JSON em PT-BR na API (BDR-003: erros consistentes).
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado.'], 401);
            }

            return null;
        });

        // 404 sempre JSON em PT-BR na API (não vazar existência, §4 do AGENTS.md).
        // O Laravel converte ModelNotFoundException em NotFoundHttpException
        // antes dos renders — por isso inspecionamos a exceção anterior.
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $previous = $exception->getPrevious();

            $message = $previous instanceof ModelNotFoundException
                ? match ($previous->getModel()) {
                    Product::class => 'Produto não encontrado.',
                    default => 'Recurso não encontrado.',
                }
            : 'Rota não encontrada.';

            return response()->json(['message' => $message], 404);
        });
    })->create();
