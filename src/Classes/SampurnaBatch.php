<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Sampurna;

class SampurnaBatch
{
    private static ?self $instance = null;
    private string $batches_vault_path;

    public function __construct()
    {
        $this->batches_vault_path = config('sampurna.sampurna_vault') . '/batches/';
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $name, array $data): void
    {
        file_put_contents(
            $this->batches_vault_path . $name . '.json',
            sampurna()->helpers()->toJson($data)
        );
    }

    public function get(string $name): array
    {
        return sampurna()->helpers()->fromJson(
            file_get_contents($this->batches_vault_path . $name . '.json')
        );
    }
}