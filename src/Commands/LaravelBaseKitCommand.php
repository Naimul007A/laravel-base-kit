<?php

namespace Naimul007A\LaravelBaseKit\Commands;

use Illuminate\Console\Command;

class LaravelBaseKitCommand extends Command
{
    public $signature = 'laravel-base-kit';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
