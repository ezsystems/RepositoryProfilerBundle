<?php

namespace eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Actor;

class NullLogger extends Logger
{
    public function startExecutor( Executor $executor )
    {
        // Just do nothing…
    }

    public function stopExecutor( Executor $executor )
    {
        // Just do nothing…
    }

    public function startActor( Actor $actor )
    {
        // Just do nothing…
    }

    public function stopActor( Actor $actor )
    {
        // Just do nothing…
    }
}

