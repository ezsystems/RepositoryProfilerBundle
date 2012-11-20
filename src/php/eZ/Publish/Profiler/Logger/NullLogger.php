<?php

namespace eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Task;

class NullLogger extends Logger
{
    public function startExecutor( Executor $group )
    {
        // Just do nothing…
    }

    public function stopExecutor( Executor $group )
    {
        // Just do nothing…
    }

    public function startTask( Task $task )
    {
        // Just do nothing…
    }

    public function stopTask( Task $task )
    {
        // Just do nothing…
    }
}

