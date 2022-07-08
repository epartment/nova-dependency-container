<?php

namespace Outl1ne\NovaDependencyContainer;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\NovaServiceProviderRegistered;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Assets
        Nova::serving(function (ServingNova $event) {
            Nova::script('dependency-container', __DIR__ . '/../dist/js/entry.js');
            Nova::style('dependency-container', __DIR__ . '/../dist/css/entry.css');
        });

        // Override ActionController after NovaServiceProvider loaded
        Event::listen(NovaServiceProviderRegistered::class, function () {
            app()->bind(
                \Laravel\Nova\Http\Controllers\ActionController::class,
                \Outl1ne\DependencyContainer\Http\Controllers\ActionController::class
            );
        });
    }
}
