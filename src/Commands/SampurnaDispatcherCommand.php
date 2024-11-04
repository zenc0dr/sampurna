<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Zenc0dr\Sampurna\Classes\SampurnaDaemon;

class SampurnaDispatcherCommand extends Command
{
    protected $signature = 'sampurna:run';
    protected $description = 'Run sampurna, run...';
    public function handle(): void
    {
        sampurna()->services()->artisanBackgroundExec('sampurna:daemon');
    }
}