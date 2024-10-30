<?php

namespace Zenc0dr\Sampurna\Classes;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Exception;
use Throwable;

class SampurnaUnit
{
    private string $units_vault_path;
    private string $unit_uuid;
    private ?array $manifest_data = null;

    public function __construct(string $unit_uuid)
    {
        $this->unit_uuid = $unit_uuid;
        $this->units_vault_path = config('sampurna.sampurna_vault') . '/units';
    }

    # Создать юнита
    public function create(array $unit_data): void
    {
        $unit_path = $this->getManifestPath();
        sampurna()->helpers()
            ->toJsonFile($unit_path, $unit_data, true);
    }

    public function getManifestPath(): string
    {
        return sampurna()->helpers()
            ->checkDir("$this->units_vault_path/$this->unit_uuid.json");
    }

    # Получить манифест юнита
    public function getManifestData(): array
    {
        if ($this->manifest_data) {
            return $this->manifest_data;
        }

        try {
            $manifest_data = sampurna()->helpers()
                ->fromJsonFile("$this->units_vault_path/$this->unit_uuid.json");

        } catch (Exception $exception) {
            throw new Exception('Unable to load unit: ' . $exception->getMessage());
        }
        if (!$manifest_data) {
            throw new Exception('Unit data is empty');
        }
        return $this->manifest_data = $manifest_data;
    }

    public function remove(): void
    {
        $unit_path = sampurna()->helpers()
            ->checkDir("$this->units_vault_path/$this->unit_uuid.json");
        if (file_exists($unit_path)) {
            unlink($unit_path);
        }
    }

    # Прямой вызов
    public function exec(...$input_data)
    {
        if (!$this->unit_uuid) {
            throw new Exception('Unit uuid is required');
        }

        try {
            $unit_data = $this->getManifestData();
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
    public function dispatch(?array $batch = null, int $data_key = 0): void
    {
        $unit_data = $this->getManifestData();
        $stack_uuid = $unit_data['stack'] ?? null;
        if (!$stack_uuid) {
            throw new Exception('Stack uuid is required');
        }
        $stack_vault = sampurna()->stack($stack_uuid)->vault();
        $task_exists = $stack_vault->query('queue')
            ->where('stack_uuid', $stack_uuid)
            ->where('unit_uuid', $this->unit_uuid)
            ->where('data_key', $data_key)
            ->count();
        if (!$task_exists) {
            $stack_vault->query('queue')->insert([
                'stack_uuid' => $stack_uuid,
                'unit_uuid' => $this->unit_uuid,
                'data_key' => $data_key,
                'created_at' => now()
            ]);
            sampurna()->services()->log("Пакет $stack_uuid.$this->unit_uuid:$data_key поставлен в очередь");
            if ($batch) {
                sampurna()->batch()->set("$stack_uuid.$this->unit_uuid.$data_key", $batch);
            }
        }
    }

    # Команда запуска юнита
    public function stream(int $data_key = 0): void
    {
        $this->artisanBackgroundExec("sampurna:unit run --uuid=$this->unit_uuid:$data_key");
    }

    # Запуск юнита в фоне
    public function streamRun(int $data_key): void
    {
        $pid = getmypid();

        if (!$pid) {
            sampurna()->services()->abort('PID процесса не определён');
        }

        try {
            $unit_data = $this->getManifestData($this->unit_uuid);
            $stack_uuid = $unit_data['stack'];
            $stack = sampurna()->stack($stack_uuid);
            $stack_vault = $stack->vault();
            $stack_data = $stack->getManifestData();

            # В случае если у стэка своё отдельное хранилище
            if (isset($stack_data['vault'])) {
                $vault_path = str_replace('base_path:', base_path(), $stack_data['vault']);
                $vault_path = str_replace('storage_path:', storage_path(), $vault_path);
                config([
                    'sampurna.sampurna_vault' => $vault_path
                ]);
            }

            $queue_record = $stack_vault->query('queue')
                ->where('stack_uuid', $stack_uuid)
                ->where('unit_uuid', $this->unit_uuid)
                ->where('data_key', $data_key)
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
            # Предполагается что этот участок кода принадлежит Sampurna
        } catch (Exception | Throwable $exception) {
            $message = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            sampurna()->services()
                ->abort(
                    "Формирование вызова завершилось с ошибкой: $file:$line $message "
                );
        }
        $error = null;
        try {
            # Вызов юнита
            $unit_output = app($call_string)->{$method}($batch);
        } catch (Exception | Throwable $exception) {
            $error = $exception->getMessage();
        }

        # Если была ошибка
        if ($error) {
            $attempts_max = intval($unit_data['attempts_max']);
            $attempts_count = intval($queue_record->attempts);

            # Окончательная ошибка
            if ($attempts_count >= $attempts_max) {
                $stack_vault->query('queue')
                    ->where('id', $queue_record->id)
                    ->update([
                        'pid' => null,
                        'status' => 'error',
                        'errors' => $this->errorsMutator($queue_record, $error),
                        'after_at' => null
                    ]);
            } else {
                $stack_vault->query('queue')
                    ->where('id', $queue_record->id)
                    ->update([
                        'pid' => null,
                        'status' => 'await',
                        'errors' => $this->errorsMutator($queue_record, $error),
                        'attempts' => $attempts_count + 1,
                    ]);
            }
        # Если всё завершилось
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

    # Делает массив ошибок для записи в поле errors
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