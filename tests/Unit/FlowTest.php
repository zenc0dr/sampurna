<?php

namespace Zen\Sampura\Tests\Unit;

use Tests\TestCase;

class FlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
//        config([
//            'sampurna.sampurna_vault' => storage_path('sampurna_vault_test')
//        ]);
    }

    public function test_create_stack()
    {
        sampurna()->stack('sampurna_test_stack')->remove();
        sampurna()->stack('sampurna_test_stack')->create('Sampurna stack test');
        $this->assertTrue(
            sampurna()->stack('sampurna_test_stack')
                ->getManifestData()['name'] === 'Sampurna stack test'
        );
    }

    public function test_create_unit0()
    {
        sampurna()->unit('test_unit_0')->remove();
        sampurna()->unit('test_unit_0')->create([
            'name' => 'Тестовый юнит 0',
            'call' => 'Zenc0dr.Sampurna.Tests.TestUnits.test0',
            'mode' => 'direct'
        ]);
        $this->assertTrue(
            sampurna()->unit('test_unit_0')
                ->getManifestData()['call'] === 'Zenc0dr.Sampurna.Tests.TestUnits.test0'
        );
    }

    public function test_create_unit1()
    {
        sampurna()->unit('test_unit_1')->remove();
        sampurna()->unit('test_unit_1')->create([
            'name' => 'Тестовый юнит 1',
            'stack' => 'sampurna_test_stack',
            'call' => 'Zenc0dr.Sampurna.Tests.TestUnits.test1',
            'attempts_max' => 3,
            'attempts_pause' => 10,
            'mode' => 'dispatcher' // Юнит который ставится диспетчером в очередь на выполнение
        ]);

        $this->assertTrue(
            sampurna()->unit('test_unit_1')
                ->getManifestData()['call'] === 'Zenc0dr.Sampurna.Tests.TestUnits.test1'
        );
    }

    public function test_create_unit2()
    {
        sampurna()->unit('test_unit_2')->remove();
        sampurna()->unit('test_unit_2')->create([
            'name' => 'Тестовый юнит 2',
            'stack' => 'sampurna_test_stack',
            'call' => 'Zenc0dr.Sampurna.Tests.TestUnits.test2',
            'attempts_max' => 3,
            'attempts_pause' => 10
        ]);

        $this->assertTrue(
            sampurna()->unit('test_unit_2')
                ->getManifestData()['call'] === 'Zenc0dr.Sampurna.Tests.TestUnits.test2'
        );
    }

    public function test_unit_direct_call()
    {
        $test_phrase = sampurna()->unit('test_unit_0')->exec('Test completed!');
        $this->assertTrue($test_phrase === 'Test completed!');
    }

    public function test_dispatcher()
    {
        sampurna()->dispatcher()->run();
        $record = sampurna()->stack('sampurna_test_stack')
            ->vault()->query('queue')
            ->where('stack_uuid', 'sampurna_test_stack')
            ->where('unit_uuid', 'test_unit_1')
            ->first();
        $this->assertTrue(
            $record?->data_key === 0 && $record?->status === 'ready'
        );
    }

    public function test_stream()
    {
        sampurna()->unit('test_unit_1')->stream();
    }
}