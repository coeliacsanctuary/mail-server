<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Mailcoach\Domain\Shared\Actions\SendTransactionalMailAction;
use Spatie\Mailcoach\Domain\Shared\Commands\WorkCommand as MailcoachWorkCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(EditorServiceProvider::class);

        $this->app->bind(MailcoachWorkCommand::class, \App\Console\Commands\MailcoachWorkCommand::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        $this->app->bind(SendTransactionalMailAction::class, fn () => new \App\Actions\SendTransactionalMailAction());

        Route::mailcoach('/');
    }
}
