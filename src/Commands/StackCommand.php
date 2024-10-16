<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Services\SampurnaStackService;

class StackCommand extends Command
{
    protected $signature = 'sampurna:stack {action?} {--name=}';
    protected $description = 'Run patch';
    public function handle()
    {
        $action = $this->argument('action');

        if (!$action) {
            $this->dropError('Action not specified');
        }

        if ($action === 'create') {
            $name = $this->option('name');
            if (!$name) {
                $this->dropError('Name not specified');
            }
            $stack = new SampurnaStackService();
            $stack->create($name);
        }
    }

    private function dropError(string $message)
    {
        exit("Error: $message " . PHP_EOL);
    }
}