<?php

use App\Exceptions\ErroResposta;
use App\Exceptions\NegocioException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API pura, sem tela de login: por padrão o Laravel registra um
        // redirecionamento para a rota "login" quando um guest é
        // bloqueado e a requisição não "espera" JSON (ex: sem o header
        // Accept: application/json, como um curl simples) — e como essa
        // rota não existe aqui, isso quebraria com 500 em vez de 401.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Equivalente ao GlobalExceptionHandler.java: erros de validação
        // (@Valid) retornam 400 com o mapa de campo -> mensagem.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $erros = [];
            foreach ($e->errors() as $campo => $mensagens) {
                $erros[$campo] = $mensagens[0];
            }

            return ErroResposta::responder(
                $request,
                422,
                'Erro de Validação',
                'Um ou mais campos contêm valores inválidos.',
                $erros,
            );
        });

        // RuntimeException / regra de negócio (AgendamentoService,
        // OficinaService, etc. lançam NegocioException).
        $exceptions->render(function (NegocioException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ErroResposta::responder($request, 400, 'Regra de Negócio Violada', $e->getMessage());
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ErroResposta::responder($request, 404, 'Recurso Não Encontrado', 'O recurso solicitado não foi encontrado.');
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ErroResposta::responder($request, 404, 'Recurso Não Encontrado', 'A rota ou recurso solicitado não foi encontrado.');
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ErroResposta::responder($request, 401, 'Não Autenticado', 'É necessário um token JWT válido para acessar este recurso.');
        });
    })->create();
