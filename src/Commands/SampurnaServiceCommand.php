<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SampurnaServiceCommand extends Command
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
            Artisan::call('sampurna:stack create', [
                '--uuid' => 'test.stack',
                '--name' => 'Тестовый стек',
            ]);
        }
    }

    private function dropError(string $message)
    {
        $this->error($message);
        exit(0);
    }
}