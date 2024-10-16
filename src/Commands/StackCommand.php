<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Services\SampurnaStackService;

class StackCommand extends Command
{
    protected $signature = 'sampurna:stack {action?} {--uuid=}';
    protected $description = 'Run patch';
    public function handle()
    {
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
    }

    private function dropError(string $message)
    {
        exit("Error: $message " . PHP_EOL);
    }
}