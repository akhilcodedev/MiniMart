<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\UserPermission\Http\Middleware\DynamicRoles;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class
        ]);
            //
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'dynamic.roles' => \Modules\UserPermission\App\Http\Middleware\DynamicRoles::class,



        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(

            function (Request $request, Throwable $e) {
                if ($request->is('api/*')) {
                    return customHandleApiException($request, $e);
                }

                return $request->expectsJson();
            }

        );

    })->create();



function customHandleApiException($request, Throwable $exception)
{
    if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
        return response()->json([
            'success' => false,
            'data' => [
                'http_status' => 401
            ],
            'message' => $exception->getMessage()
        ], 401);
    }

    return customApiResponseHandler($exception);
}
function customApiResponseHandler($exception)
{
    if (method_exists($exception, 'getStatusCode')) {
        $statusCode = $exception->getStatusCode();
    } else {
        $statusCode = 500;
    }

    $response = [
        'success' => false,
        'data' => []
    ];

    switch ($statusCode) {
        case 401:
            $response['message'] = 'Unauthorized';
            break;
        case 403:
            $response['message'] = 'Forbidden';
            break;
        case 404:
            $response['message'] = 'Not Found';
            break;
        case 405:
            $response['message'] = 'Method Not Allowed';
            break;
        case 422:
            $response['message'] = $exception->original['message'];
            $response['data']['errors'] = $exception->original['errors'];
            break;
        default:
            $response['message'] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
            break;
    }

    if (config('app.debug')) {
        $response['data']['trace'] = (property_exists($exception, 'trace')) ? $exception->getTrace() : null;
        $response['data']['code'] = (property_exists($exception, 'code')) ? $exception->getCode() : null;
    }

    $response['data']['http_status'] = $statusCode;

    return response()->json($response, $statusCode);
}

