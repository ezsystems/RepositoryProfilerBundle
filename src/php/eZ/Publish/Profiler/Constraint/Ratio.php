<?php

namespace eZ\Publish\Profiler\Constraint;

use eZ\Publish\Profiler\Constraint;
use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Logger;
use eZ\Publish\Profiler\Task;

class Ratio extends Constraint
{
    public $ratio;

    public function __construct( Task $task, $ratio )
    {
        parent::__construct( $task );
        $this->ratio = $ratio;
    }

    public function run( Executor $executor, Logger $logger )
    {
        if ( ( mt_rand() / mt_getrandmax() ) < $this->ratio )
        {
            $logger->startTask( $this->task );
            $executor->visitTask( $this->task );
            $logger->stopTask( $this->task );
        }
    }
}

