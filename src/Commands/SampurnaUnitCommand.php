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
            $run = explode(':', $uuid);
            $unit_uuid = $run[0];
            $data_key = $run[1];
            sampurna()->unit($unit_uuid)->streamRun($data_key);
        }
    }
}