<?php
namespace Naimul007A\LaravelBaseKit;
use Illuminate\Support\ServiceProvider;
use Naimul007A\LaravelBaseKit\Commands\InstallKit;

class LaravelBaseKitServiceProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        // Publish Requests
        $this->publishes([
            __DIR__ . '/Http/Requests/' => base_path('app/Http/Requests'),
        ], 'base-kit-requests');
        // Publish API Exceptions
        $this->publishes([
            __DIR__ . '/Exceptions/' => base_path('app/Exceptions'),
        ], 'base-kit-exceptions-api');
        //Services publishing
        $this->publishes([
            __DIR__ . '/Services/Base/Web/' => base_path('app/Services/Base'),
        ], 'base-kit-services');
        //Services publishing
        $this->publishes([
            __DIR__ . '/Services/Base/Api/' => base_path('app/Services/Base'),
        ], 'base-kit-services-api');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallKit::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        //
    }
}
