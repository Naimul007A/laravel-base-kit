<?php
namespace Naimul007A\LaravelBaseKit\Commands;

use Illuminate\Console\Command;

class InstallKit extends Command {
/**
 * The name and signature of the console command.
 *
 * @var string
 */
    protected $signature = "base-kit:install {--api : Publish API-specific request classes}";

/**
 * The console command description.
 *
 * @var string
 */
    protected $description = 'Install the Laravel Base Kit package (Publish config, views, libs, migrations, dependencies and components). Use --api to publish API request classes into app/Http/Requests/Api';

/**
 * Execute the console command.
 */
    public function handle() {
        $this->info('Starting Laravel Base Kit installation...');

        // 1. Publish Requests (Mandatory)
        $this->info('Publishing Requests...');
        $this->call('vendor:publish', [
            '--tag'   => 'base-kit-requests',
            '--force' => true,
        ]);

        // 2.  publish IF API
        if ($this->option('api')) {
            // Publish API Exceptions
            $this->call('vendor:publish', [
                '--tag'   => 'base-kit-exceptions-api',
                '--force' => true,
            ]);
            // Publish API Services
            $this->info('Publishing Services...');
            $this->call('vendor:publish', [
                '--tag'   => 'base-kit-services-api',
                '--force' => true,
            ]);
        } else {
            // 3. Publish Web Services
            $this->info('Publishing Services...');
            $this->call('vendor:publish', [
                '--tag'   => 'base-kit-services',
                '--force' => true,
            ]);
        }
        $this->info('Laravel Base Kit installation completed successfully!');
    }
}
