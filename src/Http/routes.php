<?php

use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/sampurna.api.{class}:{method}', function ($class, $method) {
    return app("Zenc0dr\Sampurna\Api\\$class")->{$method}();
});