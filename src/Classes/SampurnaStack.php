<?php

namespace Zenc0dr\Sampurna\Classes;

use Illuminate\Database\Schema\Blueprint;

class SampurnaStack
{
    private string $stack_uuid;

    public function __construct(string $stack_uuid)
    {
        $this->stack_uuid = $stack_uuid;
    }

    public function list()
    {
        dd('ookey list');
    }

    private function createStackQueueVault(): void
    {
        $stack_vault_name = "$this->stack_uuid.queue";
        sampurna()->vault($stack_vault_name)
            ->create(function ($schema) {
                $schema->create('queue', function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('stack_uuid');
                    $table->string('name');
                    $table->integer('key')->unsigned()->default(0);
                    $table->string('status')->default('ready');
                    $table->integer('sec')->default(0);
                    $table->mediumText('errors')->nullable();
                    $table->integer('attempts')->default(0);
                    $table->timestamp('created_at');
                    $table->timestamp('start_at')->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->timestamp('after_at')->nullable();
                    $table->string('pid')->nullable();
                });
                $schema->create('await', function (Blueprint $table) {
                    $table->unsignedBigInteger('queue_id');
                    $table->string('name');
                    $table->integer('key')->unsigned()->default(0);
                    $table->timestamp('created_at');
                    $table->string('status')->default('wait');
                });
            });
    }

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

    public function vault()
    {
        return sampurna()->vault("$this->stack_uuid.queue");
    }
}