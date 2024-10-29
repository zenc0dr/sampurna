<?php

namespace Zen\Sampura\Tests\Unit;

use Tests\TestCase;

class FlowTest extends TestCase
{
    private static string $test_stack_uuid;
    private static string $test_stack_path;

    protected function setUp(): void
    {

        self::$test_stack_uuid = 'sampurna_test_stack';
        self::$test_stack_path = '/app/storage/sampurna_vault/stacks/' . self::$test_stack_uuid . '.json';

        parent::setUp();
        if (file_exists(self::$test_stack_path)) {
            unlink(self::$test_stack_path);
        }
    }

    public function test_create_stack()
    {
        sampurna()->stack(self::$test_stack_uuid)->create('Тестовый стэк');



        $this->assertTrue(
            file_exists(self::$test_stack_path)
        );
    }
}