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
