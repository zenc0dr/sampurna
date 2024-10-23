<?php

namespace Zenc0dr\Sampurna\Api;

use App\Services\Parsers\Waterway\WaterwayParser;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

class Debug
{
    # http://sampurna.azimut.dc/sampurna.api.Debug:test
    public function test()
    {
        $ww = new WaterwayParser();
        $ww->getCruises();
    }
}