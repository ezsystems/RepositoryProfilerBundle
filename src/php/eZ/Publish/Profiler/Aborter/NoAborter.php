<?php

namespace eZ\Publish\Profiler\Aborter;

use eZ\Publish\Profiler\Aborter;

class NoAborter extends Aborter
{
    public function shouldAbort()
    {
        return false;
    }
}

