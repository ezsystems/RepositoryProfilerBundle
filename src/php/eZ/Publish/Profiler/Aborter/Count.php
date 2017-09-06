<?php

namespace eZ\Publish\Profiler\Aborter;

use eZ\Publish\Profiler\Aborter;

class Count extends Aborter
{
    protected $count = 0;

    protected $iterationCount;

    public function __construct($iterationCount)
    {
        $this->iterationCount = $iterationCount;
    }

    public function shouldAbort()
    {
        return ++$this->count >= $this->iterationCount;
    }
}
