<?php

namespace App\Providers;

use App\Observers\TextReviewObserver;
use App\Services\ReviewableText;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        foreach (ReviewableText::types() as $model) {
            $model::observe(TextReviewObserver::class);
        }
    }
}
