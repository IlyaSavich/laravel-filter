<?php

namespace Savich\Filter;

use Illuminate\Support\ServiceProvider;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../../config/laravel-filter.php.php' => config_path('laravel-filter.php'),
        ], 'config');
    }
}