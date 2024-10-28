<?php

namespace Zenc0dr\Sampurna\migrations;

use Illuminate\Database\Schema\Blueprint;

class StackMigration
{
    public function migrate(string $stack_uuid): void
    {
        $stack_vault_name = "$stack_uuid.queue";
        sampurna()->vault($stack_vault_name)
            ->create(function ($schema) {
                $schema->create('queue', function (Blueprint $table) {
                    $table->increments('id'); # Идентификатор юнита
                    $table->string('stack_uuid');
                    $table->string('unit_uuid');
                    $table->integer('data_key')->unsigned()->default(0);
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
            });
    }
}