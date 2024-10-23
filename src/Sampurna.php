<?php

namespace Zenc0dr\Sampurna;

use Zenc0dr\Sampurna\Traits\SingletonTrait;
use Zenc0dr\Sampurna\Classes\SampurnaHelpers;
use Zenc0dr\Sampurna\Classes\SampurnaServices;
use Zenc0dr\Sampurna\Classes\SampurnaVault;

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
}
