<?php

namespace Zenc0dr\Sampurna\Commands;

use App\Services\Parsers\Waterway\WaterwayParser;
use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Services\SampurnaStackService;

class StackCommand extends Command
{
    protected $signature = 'sampurna {action?} {--uuid=}';
    protected $description = 'Run patch';
    public function handle()
    {
        sampurna()->services()->sessionStorageSet('sampurna.log.echo', true);
        $action = $this->argument('action');

        if (!$action) {
            $this->dropError('Action not specified');
        }

        if ($action === 'create') {
            $uuid = $this->option('uuid');
            if (!$uuid) {
                $uuid = uniqid();
            }
            $stack = new SampurnaStackService();
            $stack->create($uuid);
        }

        if ($action === 'debug') {
            $ww = new WaterwayParser();
            //$ww->dispatcher();
            $ww->getCruises();
        }
    }

    private function dropError(string $message)
    {
        exit("Error: $message " . PHP_EOL);
    }
}