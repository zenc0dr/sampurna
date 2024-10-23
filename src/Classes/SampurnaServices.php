<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaServices
{
    use SingletonTrait;

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

        file_put_contents(
            $log_path,
            $message,
            FILE_APPEND
        );
    }
}