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

    }

    # http://sampurna.azimut.dc/sampurna.api.Debug:dispatcherTest
    public function dispatcherTest()
    {
        sampurna()->dispatcher()->run();
        $records = sampurna()->stack('sampurna_test_stack')->vault()->query('queue')
            ->get();

        foreach ($records as $record) {
            echo "$record->id:$record->status ($record->attempts)<br>";
        }
    }
}