<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        View::composer(['layouts.public', 'layouts.panel', 'welcome', '*'], function ($view) {
            try {
                $cartService = app(CartService::class);
                $view->with('cartCount', $cartService->getCartCount());
            } catch (\Throwable $e) {
                $view->with('cartCount', 0);
            }
        });
    }
}
