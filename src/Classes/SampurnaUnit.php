<?php

namespace Zenc0dr\Sampurna\Classes;

class SampurnaUnit
{
    public $units_vault_path;

    public function __construct()
    {
        $this->units_vault_path = config('sampurna.sampurna_vault');
    }

    public function run(string $unit_name)
    {

    }
}