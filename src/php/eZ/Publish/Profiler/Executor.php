<?php

namespace eZ\Publish\Profiler;

abstract class Executor
{
    public $constraints;

    public $aborter;

    public function __construct( array $constraints, Aborter $aborter = null )
    {
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
        $i = 0;
        do {
            echo '.';
            if ( ++$i >= 80)
            {
                $i = 0;
                echo PHP_EOL;
            }

            foreach ( $this->constraints as $constraint )
            {
                $constraint->run( $this );
            }
        } while ( !$this->aborter->shouldAbort() );
    }

    public function visitTask( Task $task )
    {
        $this->visitActor( $task->getNext() );
    }

    abstract public function visitActor( Actor $task );
}

