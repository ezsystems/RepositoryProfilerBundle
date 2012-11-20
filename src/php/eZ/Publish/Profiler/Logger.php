<?php

namespace eZ\Publish\Profiler;

abstract class Logger
{
    abstract public function startExecutor( Executor $executor );

    abstract public function stopExecutor( Executor $executor );

    abstract public function startActor( Actor $actor );

    abstract public function stopActor( Actor $actor );
}

