<?php

namespace Zenc0dr\Sampurna\Commands;

use Illuminate\Console\Command;
use Zenc0dr\Sampurna\Classes\SampurnaStack;

class SampurnaStackCommand extends Command
{
    protected $signature = 'sampurna:stack {action} {--uuid=} {--stack_data=}';
    protected $description = 'Sampurna stacks control';

    public function handle(): void
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
                sampurna()->helpers()->fromJson($this->option('stack_data'))
            );

            $this->line("Sampurna stack uuid:$stack_uuid created");
        }

        if ($action === 'list') {
            $sampurna_vault = config('sampurna.sampurna_vault');
            $stack_files = sampurna()->helpers()->filesCollection("$sampurna_vault/stacks");
            $unit_files = sampurna()->helpers()->filesCollection("$sampurna_vault/units");

            $tree = [];
            foreach ($unit_files as $unit_file) {
                $unit_data = sampurna()
                    ->helpers()
                    ->fromJsonFile($unit_file['path']);
                if (!isset($unit_data['stack'])) {
                    continue;
                }
                $unit_uuid = pathinfo($unit_file['name'], PATHINFO_FILENAME);
                $stack_uuid = $unit_data['stack'];
                $tree[$stack_uuid][] = [
                    'uuid' => $unit_uuid,
                    'name' => $unit_data['name'],
                ];
            }

            foreach ($stack_files as $stack_file) {
                $stack_data = sampurna()
                    ->helpers()
                    ->fromJsonFile($stack_file['path']);
                $stack_uuid = pathinfo($stack_file['name'], PATHINFO_FILENAME);
                $stack_name = $stack_data['name'];
                $stack_units = $tree[$stack_uuid] ?? [];
                $units_count = count($stack_units);
                $this->line("$stack_uuid : $stack_name ($units_count)");
                if ($stack_units) {
                    $this->line(" - uuid : name");
                    $this->line("--------------");
                    foreach ($stack_units as $unit) {
                        $this->line("- {$unit['uuid']} : {$unit['name']}");
                    }
                }
            }
        }
    }
}