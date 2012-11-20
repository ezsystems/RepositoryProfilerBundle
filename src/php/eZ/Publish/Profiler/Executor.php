<?php

namespace eZ\Publish\Profiler;

abstract class Executor
{
    public $constraints;

    public $logger;

    public $aborter;

    public function __construct( array $constraints, Logger $logger = null, Aborter $aborter = null )
    {
        $this->logger = $logger ?: new Logger\NullLogger();
        $this->aborter = $aborter ?: new Aborter\NoAborter();
        foreach ( $constraints as $constraint )
        {
            $this->addConstraint( $constraint );
        }
    }

    public function addConstraint( Constraint $constraint )
    {
        $this->constraints[] = $constraint;
    }

    public function run()
    {
        $this->logger->startExecutor( $this );
        do {
            foreach ( $this->constraints as $constraint )
            {
                $constraint->run( $this, $this->logger );
            }
        } while ( !$this->aborter->shouldAbort() );
        $this->logger->stopExecutor( $this );
    }

    public function visitTask( Task $task )
    {
        $this->visitActor( $task->getNext() );
    }

    abstract public function visitActor( Actor $task );
}

