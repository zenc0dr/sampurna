<?php

namespace Zenc0dr\Sampurna\Classes;

use Exception;
use Throwable;

class SampurnaUnit
{
    private string $units_vault_path;
    private string $unit_name;

    public function __construct(string $unit_name = null, array $input_data = [])
    {
        $this->unit_name = $unit_name;
        $this->input_data = $input_data;
        $this->units_vault_path = config('sampurna.sampurna_vault') . '/units';
    }

    public function run(string $unit_name = null, mixed $input_data = [])
    {
        $unit_name = $unit_name ?? $this->unit_name;
        if (!$unit_name) {
            throw new Exception('Unit name is required');
        }

        $unit_data = $this->readUnitData($unit_name);

        $call_string = $unit_data['call'];
        $call_string = explode('.', $call_string);
        $method = array_pop($call_string);
        $call_string = join('\\', $call_string);
        return app($call_string)->{$method}($input_data);
    }

    public function stream(string $unit_name = null, array $batch = [], string $vault_key = null)
    {

    }

    private function readUnitData(string $unit_name): array
    {
        try {
            $unit_data = sampurna()->helpers()->fromJson(
                file_get_contents(
                    "$this->units_vault_path/$unit_name.json"
                )
            );
        } catch (Exception $exception) {
            throw new Exception('Unable to load unit: ' . $exception->getMessage());
        }
        if (!$unit_data) {
            throw new Exception('Unit data is empty');
        }
        return $unit_data;
    }
}