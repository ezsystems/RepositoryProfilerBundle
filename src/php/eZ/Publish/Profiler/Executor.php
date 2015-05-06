<?php

namespace eZ\Publish\Profiler;

abstract class Executor
{
    protected $logger;

    public function __construct( Logger $logger = null )
    {
        $this->logger = $logger ?: new Logger\NullLogger();
    }

    public function run( array $constraints, Aborter $aborter )
    {
        $this->logger->startExecutor( $this );
        do {
            foreach ( $constraints as $constraint )
            {
                $constraint->run( $this, $this->logger );
            }
        } while ( !$aborter->shouldAbort() );
        $this->logger->stopExecutor( $this );
    }

    public function visitTask( Task $task )
    {
        $actor = $task->getNext();

        $this->logger->startActor( $actor );
        $this->visitActor( $actor );
        $this->logger->stopActor( $actor );
    }

    abstract public function visitActor( Actor $task );
}

