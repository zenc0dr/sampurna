<?php

namespace Zenc0dr\Sampurna\Classes;

use Exception;

class SampurnaServices
{
    private static ?self $instance = null;
    private static array $session_runtime_storage = [];

    public function __construct()
    {
        # Тут происходит инициализация сессии
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function sessionStorageGet(string $key): mixed
    {
        return self::$session_runtime_storage[$key] ?? null;
    }

    public function sessionStorageSet(string $key, mixed $value): void
    {
        self::$session_runtime_storage[$key] = $value;
    }

    public function log(
        string | array $message,
        string $type = 'info',
        bool $timestamp = true,
        string $log_path = null
    ): void {
        $log_path = $log_path ?? sampurna()->helpers()->checkDir(
            config('sampurna.sampurna_vault') . '/logs/sampurna.log'
        );

        if (is_array($message)) {
            $message = sampurna()->helpers()->toJson($message, true);
        }

        $type = strtoupper($type);

        if ($timestamp) {
            $time = date('Y-m-d H:i:s');
            $message = "[$time] sampurna.$type: $message";
        }

        $message .= PHP_EOL;

        if (sampurna()->services()->sessionStorageGet('sampurna.log.echo')) {
            echo $message;
        }

        # Сохранить событие в лог
        file_put_contents(
            $log_path,
            $message,
            FILE_APPEND
        );
    }

    public function abort(string $error_message)
    {
        $this->log($error_message, 'error');
        throw new Exception($error_message);
    }

    public function artisanBackgroundExec($cli_command): void
    {
        $php_path = env('SAMPURNA_PHP_PATH', 'php');
        $nohup_enable = env('SAMPURNA_NOHUP_ENABLE', false);

        $dir = base_path();
        $output = '/dev/null';
        $output_errors = env('SAMPURNA_DAEMON_ERRORS', '/dev/null');;
        $cli_command = "$php_path $dir/artisan $cli_command >$output 2>$output_errors &";

        if ($nohup_enable) {
            $cli_command = "nohup $cli_command";
        }

        shell_exec($cli_command);
        $this->log("Выполнена команда $cli_command");
    }

    public function pidIsActive(int $pid): bool
    {
        return posix_kill($pid, 0);
    }
}