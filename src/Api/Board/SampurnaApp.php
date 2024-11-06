<?php

namespace Zenc0dr\Sampurna\Api\Board;

use Illuminate\Support\Facades\View;

class SampurnaApp
{
    # http://sampurna.azimut.dc/sampurna.api.Board.SampurnaApp:board
    public function board()
    {
        return View::file(base_path('vendor/zenc0dr/sampurna/src/views/sampurna.blade.php'));
    }
}