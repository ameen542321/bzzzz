<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Middlewares
use App\Http\Middleware\MiddlewareTrace;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\CheckSubscriptionActive;
use App\Http\Middleware\SubscriptionWarning;
use App\Http\Middleware\CheckStoreStatus;
use App\Http\Middleware\CheckStoreAccess;
use App\Http\Middleware\NoAccess;
use App\Http\Middleware\CheckUserSuspended;
use App\Http\Middleware\RedirectActiveUser;
use App\Http\Middleware\RedirectIfAuthenticatedToDashboard;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\AccountantAuth;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | 1) Global Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->append(MiddlewareTrace::class);

        /*
        |--------------------------------------------------------------------------
        | 2) Web Middleware Group
        |--------------------------------------------------------------------------
        */
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3) Route Middleware Aliases
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'is.admin'   => IsAdmin::class,
            'is.user'    => IsUser::class,
            'subscription.active'  => CheckSubscriptionActive::class,
            'subscription.warning' => SubscriptionWarning::class,
            'store.active' => CheckStoreStatus::class,
            'store.access' => CheckStoreAccess::class,
            'check.suspended' => CheckUserSuspended::class,
            'active.welcome' => RedirectActiveUser::class,
            'redirect.dashboard' => RedirectIfAuthenticatedToDashboard::class,
            'accountant.auth' => AccountantAuth::class,
            'no.access' => NoAccess::class,
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
