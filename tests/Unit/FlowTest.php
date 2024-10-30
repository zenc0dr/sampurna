<?php

namespace Zen\Sampura\Tests\Unit;

use Tests\TestCase;

class FlowTest extends TestCase
{
    private static string $test_stack_uuid;
    private static string $test_stack_path;

    public function test_create_stack()
    {
        sampurna()->stack('sampurna_test_stack')->remove();
        sampurna()->stack('sampurna_test_stack')->create('Sampurna stack test');
        $this->assertTrue(
            sampurna()->stack('sampurna_test_stack')
                ->getStackData()['name'] === 'Sampurna stack test'
        );
    }

    public function test_create_unit1()
    {
        sampurna()->unit('test_unit_1')->remove();
        sampurna()->unit('test_unit_1')->create([
            'name' => 'Тестовый юнит 1',
            'stack' => 'sampurna_test_stack',
            'call' => 'Zen.Sampurna.Tests.TestUnits.test1',
            'attempts_max' => 3,
            'attempts_pause' => 10,
            'mode' => 'dispatcher' // Юнит который ставится диспетчером в очередь на выполнение
        ]);

        $this->assertTrue(
            sampurna()->unit('test_unit_1')
                ->getUnitData()['call'] === 'Zen.Sampurna.Tests.TestUnits.test1'
        );
    }

    public function test_dispatch()
    {
        
    }
}