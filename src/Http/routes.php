<?php

use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/sampurna.api.{class}:{method}', function ($class, $method) {
    $class = str_replace('.', '\\', $class);
    return app("Zenc0dr\Sampurna\Api\\$class")->{$method}();
});