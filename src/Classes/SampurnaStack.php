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

    # Типовое хранилище стэка
    private function createStackQueueVault(): void
    {
        sampurna()->migrate('StackMigration', $this->stack_uuid);
    }

    # Создание нового стека с установками по умолчанию
    public function create(string $name = null): bool
    {
        $helpers = sampurna()->helpers();
        $sampurna_vault = config('sampurna.sampurna_vault');
        $scheme_path = $helpers->checkDir($sampurna_vault . "/stacks/$this->stack_uuid.json");
        $new_stack = sampurna()->helpers()->fromJson(
            file_get_contents(
                __DIR__ . '/../resources/stacks/new_stack.json'
            )
        );
        if ($name) {
            $new_stack['name'] = $name;
        }
        file_put_contents(
            $scheme_path,
            sampurna()->helpers()->toJson($new_stack, true)
        );
        if (file_exists($scheme_path)) {
            # Создать для стэка своё хранилище
            # Каждый юнит выполняющийся в очереди должен знать своё хранилище стэка
            $this->createStackQueueVault();
            return true;
        }
        return false;
    }

    # Получение массива данных манифеста стэка
    public function getStackData(): array
    {
        $helpers = sampurna()->helpers();
        $sampurna_vault = config('sampurna.sampurna_vault');
        $scheme_path = $helpers->checkDir($sampurna_vault . "/stacks/$this->stack_uuid.json");
        return sampurna()->helpers()->fromJsonFile($scheme_path);
    }

    public function vault()
    {
        return sampurna()->vault("$this->stack_uuid.queue");
    }
}