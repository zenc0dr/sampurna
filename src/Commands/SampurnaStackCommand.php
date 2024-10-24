<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Classes\SampurnaStack;

class SampurnaStackCommand extends Command
{
    protected $signature = 'sampurna:stack {action} {--uuid=} {--name=}';
    protected $description = 'Sampurna stacks control';

    public function handle()
    {
        sampurna()->services()->sessionStorageSet('sampurna.log.echo', true);
        $action = $this->argument('action');

        if ($action === 'create') {
            $stack_uuid = $this->option('uuid');

            if (!$stack_uuid) {
                $this->error("Usage: sampurna:stack create --uuid=<uuid>");
                exit(0);
            }

            sampurna()->stack($stack_uuid)->create(
                $this->option('name')
            );

            $this->line("Sampurna stack uuid:$stack_uuid created");
        }
    }
}