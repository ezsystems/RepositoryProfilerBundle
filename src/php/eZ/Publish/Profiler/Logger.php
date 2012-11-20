<?php

namespace eZ\Publish\Profiler;

abstract class Logger
{
    abstract public function startExecutor( Executor $group );

    abstract public function stopExecutor( Executor $group );

    abstract public function startTask( Task $task );

    abstract public function stopTask( Task $task );
}

