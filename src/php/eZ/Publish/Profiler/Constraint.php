<?php

namespace eZ\Publish\Profiler;

abstract class Constraint
{
    public $task;

    public function __construct( Task $task )
    {
        $this->task = $task;
    }

    abstract public function run( Executor $executor );
}

