<?php

namespace Zenc0dr\Sampurna\Services;

use Zenc0dr\Sampurna\Traits\SampurnaHelpers;

class SampurnaStackService
{
    use SampurnaHelpers;
    private ?string $stack_code = null;

    public function __construct(string $stack_code = null)
    {
        $this->stack_code = $stack_code;
    }

    public function create(string $stack_code)
    {
        $this->stack_code = $stack_code;
        $stack_scheme = $this->fromJson(
            file_get_contents(
                __DIR__ . '/../resources/stacks/new_stack.json'
            )
        );
        $sampurna_temp = config('sampurna.sampurna_temp');

    }
}