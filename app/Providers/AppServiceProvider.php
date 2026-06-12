<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('public.*', function ($view) {
            $domain = config('app.panel_domain');

            $view->with('panelLoginUrl', $domain
                ? "https://{$domain}/login"
                : route('filament.admin.auth.login'));

            $view->with('panelRegisterUrl', $domain
                ? "https://{$domain}/register"
                : route('filament.admin.auth.register'));
        });

        foreach ([PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, PanelsRenderHook::AUTH_REGISTER_FORM_AFTER] as $hook) {
            FilamentView::registerRenderHook(
                $hook,
                fn (): string => Blade::render("@include('filament.auth.google-button')"),
            );
        }
    }
}
