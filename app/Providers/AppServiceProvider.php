<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SemanticCatalog::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('components.pagination');
        foreach (['saved', 'deleted', 'restored'] as $event) {
            \App\Models\Product::$event(fn ($product) => app(\App\Services\SemanticCatalog::class)->defer($product->id));
        }
        foreach (['saved', 'deleted'] as $event) {
            \App\Models\ProductAttributeValue::$event(fn ($value) => app(\App\Services\SemanticCatalog::class)->defer($value->product_id));
            \App\Models\Brand::$event(fn () => app(\App\Services\SemanticCatalog::class)->deferAll());
            \App\Models\Category::$event(fn () => app(\App\Services\SemanticCatalog::class)->deferAll());
            \App\Models\Attribute::$event(fn () => app(\App\Services\SemanticCatalog::class)->deferAll());
        }
        $this->app->terminating(fn () => app(\App\Services\SemanticCatalog::class)->flush());
    }
}
