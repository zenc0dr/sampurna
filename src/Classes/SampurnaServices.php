<?php

namespace Zenc0dr\Sampurna\Classes;

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
        $log_path = $log_path ?? storage_path('logs/sampurna.log');

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

        file_put_contents(
            $log_path,
            $message,
            FILE_APPEND
        );
    }
}