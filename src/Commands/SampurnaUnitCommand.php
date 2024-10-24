<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Classes\SampurnaUnit;

class SampurnaUnitCommand extends Command
{
    protected $signature = 'sampurna:unit {action} {--uuid=}';
    protected $description = 'Sampurna units control';

    public function handle()
    {
        sampurna()->services()->sessionStorageSet('sampurna.log.echo', true);
        $action = $this->argument('action');
        $uuid = $this->option('uuid');

        if ($action === 'run') {
            if (!$uuid) {
                $this->error("Usage: sampurna:unit run --uuid=<uuid>");
                exit(0);
            }
            sampurna()->unit($uuid)->dispatch();
            $this->line("Sampurna unit uuid:$uuid launched");
        }
    }
}