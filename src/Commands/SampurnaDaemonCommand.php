<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Zenc0dr\Sampurna\Classes\SampurnaDaemon;

class SampurnaDaemonCommand extends Command
{
    protected $signature = 'sampurna:daemon {action?}';
    protected $description = 'Sampurna daemon';
    public function handle(): void
    {
        $action = $this->argument('action');
        if ($action) {
            sampurna()->services()->sessionStorageSet('sampurna.log.echo', true);
            if ($action === 'enable') {
                SampurnaDaemon::enableDaemon();
            } elseif ($action === 'disable') {
                SampurnaDaemon::disableDaemon();
            }
        } else {
            SampurnaDaemon::run();
        }
    }
}