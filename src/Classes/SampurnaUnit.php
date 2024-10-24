<?php

namespace Zenc0dr\Sampurna\Classes;

use Exception;
use Throwable;

class SampurnaUnit
{
    private string $units_vault_path;
    private string $unit_uuid;

    public function __construct(string $unit_uuid)
    {
        $this->unit_uuid = $unit_uuid;
        $this->units_vault_path = config('sampurna.sampurna_vault') . '/units';
    }

    # Прямой вызов
    public function exec(...$input_data)
    {
        if (!$this->unit_uuid) {
            throw new Exception('Unit uuid is required');
        }

        try {
            $unit_data = $this->readUnitData($this->unit_uuid);
            $call_string = $unit_data['call'];
            $call_string = explode('.', $call_string);
            $method = array_pop($call_string);
            $call_string = join('\\', $call_string);
            $result = app($call_string)->{$method}(...$input_data);
        } catch (Exception | Throwable $exception) {
            throw new Exception($exception->getMessage());
        }
        return $result;
    }

    # Постановка в очередь
    public function dispatch(array $batch = [], int $data_key = 0)
    {
        $unit_data = $this->readUnitData($this->unit_uuid);
        $stack_uuid = $unit_data['stack'] ?? null;
        if ($stack_uuid) {
            $stack_vault = sampurna()->stack($stack_uuid)->vault();
            $task_exists = $stack_vault->query('queue')
                ->where('stack_uuid', $stack_uuid)
                ->where('name', $this->unit_uuid)
                ->where('key', $data_key)
                ->count();
            if (!$task_exists) {
                $stack_vault->query('queue')->insert([
                    'stack_uuid' => $stack_uuid,
                    'name' => $this->unit_uuid,
                    'key' => $data_key,
                    'created_at' => now()
                ]);
                if ($batch) {
                    sampurna()->batch()->set("$stack_uuid.$this->unit_uuid.$data_key", $batch);
                }
            }
        } else {
            // Запуск в фоне
        }
    }



    private function readUnitData(string $unit_uuid): array
    {
        try {
            $unit_data = sampurna()->helpers()->fromJson(
                file_get_contents(
                    "$this->units_vault_path/$unit_uuid.json"
                )
            );
        } catch (Exception $exception) {
            throw new Exception('Unable to load unit: ' . $exception->getMessage());
        }
        if (!$unit_data) {
            throw new Exception('Unit data is empty');
        }
        return $unit_data;
    }

    private function artisanBackgroundExec($cli_command): void
    {
        $php_path = env('SAMPURNA_PHP_PATH', 'php');
        $nohup_enable = env('SAMPURNA_NOHUP_ENABLE', false);

        $dir = base_path();
        $output = $this->output;
        $output_errors = $this->output_errors;
        $cli_command = "$php_path $dir/artisan $cli_command >$output 2>$output_errors &";

        if ($nohup_enable) {
            $cli_command = "nohup $cli_command";
        }

        shell_exec($cli_command);
    }
}