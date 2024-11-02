<?php

namespace Zenc0dr\Sampurna\Classes;

use Illuminate\Database\Schema\Blueprint;

class SampurnaStack
{
    private string $stack_uuid;

    public function __construct(?string $stack_uuid = null)
    {
        $this->stack_uuid = $stack_uuid;
    }

    public function getManifestPath(): string
    {
        $sampurna_vault = config('sampurna.sampurna_vault');
        return sampurna()->helpers()->checkDir($sampurna_vault . "/stacks/$this->stack_uuid.json");
    }

    # Создание нового стека с установками по умолчанию
    public function create(?array $stack_data = null): bool
    {
        $helpers = sampurna()->helpers();
        $stack_manifest_path = $this->getManifestPath();
        $new_stack = $stack_data ?? $helpers
            ->fromJsonFile(
                __DIR__ . '/../resources/stacks/new_stack.json'
            );

        $helpers->toJsonFile(
            $stack_manifest_path,
            $new_stack,
            true
        );

        # Создаётся хранилище для очереди стека
        if (file_exists($stack_manifest_path)) {
            # Создать хранилище стэка
            sampurna()->migrate('StackMigration', $this->stack_uuid);
            return true;
        }
        return false;
    }

    public function remove(): void
    {
        $stack_manifest_path = $this->getManifestPath();
        if (file_exists($stack_manifest_path)) {
            unlink($stack_manifest_path);
        }
        $this->vault()->truncate();
    }

    # Получение массива данных манифеста стэка
    public function getManifestData(): array
    {
        $helpers = sampurna()->helpers();
        $sampurna_vault = config('sampurna.sampurna_vault');
        $scheme_path = $helpers->checkDir($sampurna_vault . "/stacks/$this->stack_uuid.json");
        return sampurna()->helpers()->fromJsonFile($scheme_path);
    }

    public function vault(): SampurnaVault
    {
        return sampurna()->vault("$this->stack_uuid.queue");
    }
}