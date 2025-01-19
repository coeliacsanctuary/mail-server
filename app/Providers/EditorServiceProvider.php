<?php

declare(strict_types=1);

namespace App\Providers;

use App\Editor\Editor;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Http;
use Spatie\Mailcoach\Mailcoach;

class EditorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Http::macro('coeliac', fn () => Http::baseUrl(config('services.coeliac.url'))->acceptJson());
    }

    public function boot(): void
    {
        Livewire::component('coeliac-editor', Editor::class);

        if ($this->app->runningInConsole()) {
            return;
        }

        Mailcoach::editorStyle(Editor::class, Vite::asset('resources/css/app.css'));
    }
}
