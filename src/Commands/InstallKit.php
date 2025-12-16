<?php
namespace Naimul007A\LaravelBaseKit\Commands;

use Illuminate\Console\Command;

class InstallKit extends Command {
/**
 * The name and signature of the console command.
 *
 * @var string
 */
    protected $signature = 'base-kit:install';

/**
 * The console command description.
 *
 * @var string
 */
    protected $description = 'Install the Laravel Base Kit package (Publish config, views, libs, migrations, dependencies and components)';

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
    }
}
