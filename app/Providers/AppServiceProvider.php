<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
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
        JsonResource::withoutWrapping();
        // This will prevent Laravel from wrapping the response in a "data" key
        // used when you return a resource or a collection of resources. This is useful when you want to return the resource directly without the additional "data" wrapper.
        //in cases of: paginated responses, collections, or single resources, the response will be returned directly without being wrapped in a "data" key.
        }
}
