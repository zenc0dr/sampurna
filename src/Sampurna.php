<?php

namespace Zenc0dr\Sampurna;

use Zenc0dr\Sampurna\Classes\SampurnaHelpers;
use Zenc0dr\Sampurna\Traits\SingletonTrait;

class Sampurna
{
    use SingletonTrait;

    public function helpers(): SampurnaHelpers
    {
        return SampurnaHelpers::getInstance();
    }
}
