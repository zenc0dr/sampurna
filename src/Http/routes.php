<?php

use Illuminate\Support\Facades\Route;
use Zenc0dr\Sampurna\Sampurna;

Route::get('/test_sampurna', function () {
    return Sampurna::test();
});