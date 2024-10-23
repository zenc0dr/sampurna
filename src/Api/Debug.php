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
        //sampurna()->services()->sessionStorageSet('test', 'Тут что-то');
       //$this->test2();

//        $ww = new WaterwayParser();
//        $ww->getCruises();

        dd('OK?');


    }

    public function test2()
    {
        dd(
            sampurna()->services()->sessionStorageGet('test')
        );
    }
}