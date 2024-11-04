<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Exception;
use Throwable;

class SampurnaUnitCommand extends Command
{
    protected $signature = 'sampurna:unit {action} {--uuid=} {--name=} {--unit_data=}';
    protected $description = 'Sampurna units control';

    public function handle(): void
    {
        sampurna()->services()
            ->sessionStorageSet('sampurna.log.echo', true);

        $action = $this->argument('action');
        $uuid = $this->option('uuid');

        if ($action === 'create') {
            sampurna()->unit($uuid)->create(
                sampurna()->helpers()
                    ->fromJson($this->option('unit_data'))
            );
        }

        if ($action === 'run') {
            if (!$uuid) {
                $this->error("Usage: sampurna:unit run --uuid=<uuid>");
                exit(0);
            }
            $run = explode(':', $uuid);
            $unit_uuid = $run[0];
            $data_key = $run[1] ?? 0;
            try {
                sampurna()->unit($unit_uuid)->streamRun($data_key);
            } catch (Exception | Throwable $exception) {
                sampurna()->services()->log($exception->getMessage(), 'error');
            }
        }
    }
}