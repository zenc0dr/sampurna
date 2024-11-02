<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaDispatcher
{
    use SingletonTrait;

    private array $threads = [];

    public function run(): void
    {
        $units_dir = config('sampurna.sampurna_vault') . "/units";
        $files = sampurna()->helpers()->filesCollection($units_dir);
        $stacks = [];
        foreach ($files as $file) {
            $unit_uuid = preg_replace('/\.json$/', '', $file['name']);
            $unit_data = sampurna()->unit($unit_uuid)->getManifestData();
            if (isset($unit_data['stack'])) {
                if (isset($unit_data['mode'])) {
                    if ($unit_data['mode'] === 'dispatcher') {
                        sampurna()->unit($unit_uuid)->dispatch(); # Сразу поставить в очередь на выполнение
                    }
                }
                $this->threads[$unit_uuid] = isset($unit_data['threads'])
                    ? intval($unit_data['threads'])
                    : 1;
                $stacks[] = $unit_data['stack'];
            }
        }
        $stacks = array_unique($stacks);
        $stacks = array_values($stacks);

        foreach ($stacks as $stack_uuid) {
            $stack_vault = sampurna()->stack($stack_uuid)->vault();

            # Запуск юнитов
            $this->unitsHandler($stack_vault);
        }
    }

    private function unitsHandler($stack_vault): void
    {
        $units_records = $stack_vault->query('queue')
            ->whereNotIn('status', ['completed', 'error'])
            ->orderBy('created_at')
            ->orderByRaw("
                CASE 
                    WHEN status = 'ready' THEN 1
                    WHEN status = 'await' THEN 2
                    ELSE 3
                END
            ")
            ->get();

        foreach ($units_records as $units_record) {
            if ($this->threads[$units_record->unit_uuid] < 1) {
                continue;
            }
            $this->threads[$units_record->unit_uuid]--;
            $unit = sampurna()->unit($units_record->unit_uuid);
            $unit->stream($units_record->data_key);
        }
    }
}