<?php

namespace eZ\Publish\Profiler;

abstract class Aborter
{
    abstract public function shouldAbort();
}
