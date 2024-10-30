<?php

namespace Zenc0dr\Sampurna\Tests;

use Illuminate\Database\Schema\Blueprint;

class TestUnits
{
    public function test0(string $test_phrase): string
    {
        return $test_phrase;
    }

    public function test1()
    {
        sampurna()->vault('test_vault')
            ->create(function ($schema) {
                $schema->create('test_data', function (Blueprint $table) {
                    $table->string('data')->nullable();
                });
            });

        for ($i = 0; $i < 3; $i++) {
            sampurna()->services()->log("Отработал unit1 вызов: $i");
            sampurna()->unit('unit2')->dispatch([
                'batch_data' =>  "From unit1.$i"
            ], $i);
        }
    }

    public function test2(array $batch): void
    {
        $batch_data = $batch['batch_data'];
        for ($i = 0; $i < 10; $i++) {
            $batch_data['iteration'] = $i;
            sampurna()->services()->log("Отработал unit2 вызов: $i");
            sampurna()->vault('test_vault')
                ->query('test_data')
                ->insert([
                    'data' => sampurna()->helpers()->toJson($batch_data),
                ]);
        }
    }
}