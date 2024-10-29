<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaDispatcher
{
    use SingletonTrait;

    public function run(): void
    {
        $units_dir = config('sampurna.sampurna_vault') . "/units";
        $files = sampurna()->helpers()->filesCollection($units_dir);
        $stacks = [];
        foreach ($files as $file) {
            $unit_data = sampurna()->helpers()->fromJson(
                file_get_contents($file['path'])
            );
            if (isset($unit_data['stack'])) {
                $stacks[] = $unit_data['stack'];
            }
        }
        $stacks = array_unique($stacks);
        $stacks = array_values($stacks);

        foreach ($stacks as $stack_uuid) {
            $stack_vault = sampurna()->stack($stack_uuid)->vault();

            # Запуск в потоке юнитов со статусом ready
            $this->unitReadyHandle($stack_vault);
        }
    }

    private function unitReadyHandle($stack_vault)
    {
        $units_records = $stack_vault->query('queue')
            ->where('status', 'ready')
            ->orderByDesc('created_at')
            ->get();

       // $stacks = [];

        foreach ($units_records as $units_record) {
            $unit = sampurna()->unit($units_record->name);
            #$unit_data = $unit->getUnitData();
            $batch_key = "$units_record->stack_uuid.$units_record->unit_uuid.$units_record->key";
            $batch = sampurna()->batch($batch_key);
            $unit->dispatch($batch, $batch_key);


            //dd('???');

            # Тут надо запустить и пометить
            $unit->stream($units_record->name, $units_record->key);
        }
    }
}