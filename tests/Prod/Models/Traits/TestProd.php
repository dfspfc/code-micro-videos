<?php

namespace Tests\Prod\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait TestProd
{
    protected function skipTestIfNotProd($message = '')
    {
        if (!$this->isTestingProd()) {
            $this->markTestSkipped($message);
        }
    }

    protected function isTestingProd()
    {
        return env('TESTING_PROD') !== false;
    }
}