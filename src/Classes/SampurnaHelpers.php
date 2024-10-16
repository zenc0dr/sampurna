<?php

namespace Zenc0dr\Sampurna\Classes;

use Zenc0dr\Sampurna\Traits\SingletonTrait;

class SampurnaHelpers
{
    use SingletonTrait;

    #### STRINGS
    public function toJson(array $arr = [], bool $pretty_print = false, bool $no_slashes = false): ?string
    {
        if (empty($arr)) {
            return null;
        }

        $options = JSON_UNESCAPED_UNICODE
            | ($pretty_print ? JSON_PRETTY_PRINT : 0)
            | ($no_slashes ? JSON_UNESCAPED_SLASHES : 0);

        return json_encode($arr, $options);
    }

    public function fromJson(string $string, int|bool $assoc = true): array|object|null
    {
        if (empty($string)) {
            return null;
        }
        return json_decode($string, $assoc);
    }

    #### FILES
    public function checkDir(string $dir_path): string
    {
        $dirname = dirname($dir_path);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }
        return $dir_path;
    }
}