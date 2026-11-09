<?php

use App\Http\Middleware\CheckAccessTokenDeviceMiddleware;
use App\Http\Middleware\CheckAdminMiddleware;
use App\Http\Middleware\CheckCanRefreshMiddleware;
use App\Http\Middleware\CheckEmailIsNotVerifiedMiddleware;
use App\Http\Middleware\CheckLanguageMiddleware;
use App\Http\Middleware\CheckUserMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->booting(function (Application $app): void {
        $app->useLangPath(base_path('lang'));
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->append(CheckLanguageMiddleware::class);
        $middleware->alias([
            'auth.user' => CheckUserMiddleware::class,
            'auth.admin' => CheckAdminMiddleware::class,
            'check.device' => CheckAccessTokenDeviceMiddleware::class,
            'can.refresh' => CheckCanRefreshMiddleware::class,
            'verified' => EnsureEmailIsVerified::class,
            'check_language' => CheckLanguageMiddleware::class,
            'not_verified' => CheckEmailIsNotVerifiedMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $shouldReturnJson = static fn (Request $request): bool => $request->expectsJson() || $request->is('api/*');

        $setRequestLocale = static function (Request $request): void {
            $locale = $request->header('lang');

            if ($locale && in_array($locale, config('app.supported_locales', []), true)) {
                app()->setLocale($locale);
            }
        };

        $apiError = static function (
            int $status,
            string $message
        ) {

            return response()->json([
                'message' => $message,
            ], $status);
        };

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            return $apiError(
                401,
                __('You are not authenticated, please login and try again'),
            );
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            return $apiError(403, __('This action is unauthorized.'));
        });














        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $headers = $e->getHeaders();
            $retryAfterSeconds = $headers['Retry-After'] ?? 0;


            $minutes = floor($retryAfterSeconds / 60);
            $seconds = $retryAfterSeconds % 60;


            $translatedMessage = __('Too many requests. Please try again in :minutes minute(s) and :seconds second(s).', [
                'minutes' => $minutes,
                'seconds' => $seconds,
            ]);

            $setRequestLocale($request);

            return $apiError(
                429,
                $translatedMessage
            );
        });














        $exceptions->render(function (ValidationException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            return $apiError(422, $e->validator->errors()->first());
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            return $apiError(404, __('Resource Not Found'));
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);





            return $apiError(403, __($e->getMessage()));

        });

        $exceptions->render(function (HttpException $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            return $apiError(
                $e->getStatusCode(),
                $e->getMessage() === ('CSRF token mismatch.') ? __('Session expired') : $e->getMessage()
            );
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($shouldReturnJson, $apiError, $setRequestLocale) {
            if (! $shouldReturnJson($request)) {
                return null;
            }

            $setRequestLocale($request);

            Log::error('Unhandled API exception', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            return $apiError(
                222,
                __('Something went wrong, please try again later')
            );
        });
    })->create();
