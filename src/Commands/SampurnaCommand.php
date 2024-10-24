<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Classes\SampurnaStack;

class SampurnaCommand extends Command
{
    protected $signature = 'sampurna {action?} {--context=}';
    protected $description = 'Sampurna command system';
    public function handle()
    {
        sampurna()->services()->sessionStorageSet('sampurna.log.echo', true);
        $action = $this->argument('action');

        if (!$action) {
            $this->dropError('Action not specified');
        }

        if ($action === 'debug') {
            sampurna()->unit('unit1')->run();
        }
    }

    private function dropError(string $message)
    {
        exit("Error: $message " . PHP_EOL);
    }
}