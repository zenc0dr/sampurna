<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaVault
{
    use SingletonTrait;

    private array $vault_data = [];

    public function get(string $key)
    {
        if ($value = $this->getter($key)) {
            return $value;
        }
        return $this->vault_data[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->vault_data[$key] = $value;
    }

    private function getter(string $key)
    {
        return match ($key) {
            'log.path' => $this->sampurnaLogPathGetter(),
            default => null
        };
    }

    #### GETTERS
    private function sampurnaLogPathGetter()
    {
        if (isset($this->vault_data['log.path'])) {
            return $this->vault_data['log.path'];
        }
        $log_path = storage_path('logs/sampurna.log');
        sampurna()->helpers()->checkDir($log_path);
        return $this->vault_data['log.path'] = $log_path;
    }
}