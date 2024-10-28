<?php

namespace Zenc0dr\Sampurna;

use Zenc0dr\Sampurna\Traits\SingletonTrait;
use Zenc0dr\Sampurna\Classes\SampurnaHelpers;
use Zenc0dr\Sampurna\Classes\SampurnaDispatcher;
use Zenc0dr\Sampurna\Classes\SampurnaServices;
use Zenc0dr\Sampurna\Classes\SampurnaStack;
use Zenc0dr\Sampurna\Classes\SampurnaVault;
use Zenc0dr\Sampurna\Classes\SampurnaUnit;
use Zenc0dr\Sampurna\Classes\SampurnaBatch;
use Zenc0dr\Sampurna\Classes\SampurnaMigration;

class Sampurna
{
    use SingletonTrait;

    public function helpers(): SampurnaHelpers
    {
        return SampurnaHelpers::getInstance();
    }

    public function vault(string $vault_name): SampurnaVault
    {
        return new SampurnaVault($vault_name);
    }

    public function services(): SampurnaServices
    {
        return SampurnaServices::getInstance();
    }

    public function stack(string $uuid): SampurnaStack
    {
        return new SampurnaStack($uuid);
    }

    public function unit(string $unit_name)
    {
        return new SampurnaUnit($unit_name);
    }

    public function dispatcher(): SampurnaDispatcher
    {
        return SampurnaDispatcher::getInstance();
    }

    public function migrate($migration_name, mixed $context = null): void
    {
        SampurnaMigration::getInstance()->run($migration_name, $context);
    }

    public function batch(?string $name = null, ?array $data = null): SampurnaBatch|array|null
    {
        $batch_instance = SampurnaBatch::getInstance();
        if ($name && !$data) {
            return $batch_instance->get($name);
        } elseif ($name && $data) {
            $batch_instance->set($name, $data);
        } else {
            return $batch_instance;
        }
        return null;
    }
}
