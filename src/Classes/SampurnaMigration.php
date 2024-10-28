<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaMigration
{
    use SingletonTrait;

    public function run(string $migration_name, mixed $context = null): void
    {
        app('Zenc0dr\Sampurna\migrations\\'.$migration_name)->migrate($context);
    }
}