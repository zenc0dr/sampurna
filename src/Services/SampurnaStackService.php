<?php

namespace Zenc0dr\Sampurna\Services;

class SampurnaStackService
{
    private ?string $stack_code = null;

    public function __construct(string $stack_code = null)
    {
        $this->stack_code = $stack_code;
    }

    public function create(string $stack_code)
    {
        $this->stack_code = $stack_code;
        $helpers = sampurna()->helpers();
        $sampurna_temp = config('sampurna.sampurna_temp');
        $scheme_path = $helpers->checkDir($sampurna_temp . "/$stack_code.json");
        file_put_contents(
            $scheme_path,
            file_get_contents(
                __DIR__ . '/../resources/stacks/new_stack.json'
            )
        );
    }
}