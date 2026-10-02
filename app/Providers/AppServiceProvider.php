<?php

namespace App\Providers;

use App\Enums\Competition;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ナビの競技タブを全ページで表示できるよう共通で注入
        View::composer('layouts.app', function ($view) {
            $view->with('competitions', Competition::cases());
        });
    }
}
