<?php

namespace Zenc0dr\Sampurna\Classes;

use Illuminate\Database\Query\Builder as QueryBuilder;
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
    public function dispatch(?array $batch = null, int $data_key = 0)
    {
        $unit_data = $this->readUnitData($this->unit_uuid);
        $stack_uuid = $unit_data['stack'] ?? null;
        if (!$stack_uuid) {
            throw new Exception('Stack uuid is required');
        }
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
            sampurna()->services()->log("Пакет $stack_uuid.$this->unit_uuid:$data_key поставлен в очередь");
            if ($batch) {
                sampurna()->batch()->set("$stack_uuid.$this->unit_uuid.$data_key", $batch);
            }
        }
    }

    # Команда запуска юнита
    public function stream(string $unit_uuid, int $data_key = 0): void
    {
        $this->artisanBackgroundExec("sampurna:unit run --uuid=$unit_uuid:$data_key");
    }

    # Запуск юнита в фоне
    public function streamRun(int $data_key): void
    {
        $pid = getmypid();

        if (!$pid) {
            sampurna()->services()->abort('PID процесса не определён');
        }

        try {
            $unit_data = $this->readUnitData($this->unit_uuid);
            $stack_uuid = $unit_data['stack'];
            $stack_vault = sampurna()->stack($stack_uuid)->vault();
            $queue_record = $stack_vault->query('queue')
                ->where('stack_uuid', $stack_uuid)
                ->where('name', $this->unit_uuid)
                ->where('key', $data_key)
                ->first();
            $stack_vault->query('queue')
                ->where('id', $queue_record->id)
                ->update([
                    'pid' => $pid,
                    'attempts' => $queue_record->attempts + 1,
                    'start_at' => now(),
                    'status' => 'process'
                ]);
            $batch = sampurna()->batch("$stack_uuid.$this->unit_uuid.$data_key");
            $call_string = $unit_data['call'];
            $call_string = explode('.', $call_string);
            $method = array_pop($call_string);
            $call_string = join('\\', $call_string);
        } catch (Exception | Throwable $exception) {
            sampurna()->services()
                ->abort(
                    'Формирование вызова завершилось с ошибкой: ' .$exception->getMessage()
                );
        }
        $error = null;
        try {
            app($call_string)->{$method}($batch);
        } catch (Exception | Throwable $exception) {
            $error = $exception->getMessage();
        }

        if ($error) {
            $stack_vault->query('queue')
                ->where('id', $queue_record->id)
                ->update([
                    'pid' => null,
                    'errors' => $this->errorsMutator($queue_record, $error)
                ]);
        } else {
            $stack_vault->query('queue')
                ->where('id', $queue_record->id)
                ->update([
                    'pid' => null,
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
        }
    }

    private function errorsMutator(object $record, string $error): string
    {
        $errors = [];
        if ($record->errors) {
            $errors = sampurna()->helpers()->fromJson($record->errors);
        }
        $errors[] = [
            'time' => now()->format('Y-m-d H:i:s'),
            'error' => $error,
        ];
        return sampurna()->helpers()->toJson($errors);
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
        $output = '/dev/null';
        $output_errors = '/dev/null';
        $cli_command = "$php_path $dir/artisan $cli_command >$output 2>$output_errors &";

        if ($nohup_enable) {
            $cli_command = "nohup $cli_command";
        }

        shell_exec($cli_command);
        sampurna()->services()->log("Выполнена команда $cli_command");
    }
}