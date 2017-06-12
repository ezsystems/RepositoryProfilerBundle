<?php

namespace eZ\Publish\Profiler;

class Executor
{
    protected $actorHandler;

    protected $logger;

    public function __construct( Actor\Handler $actorHandler, Logger $logger = null )
    {
        $this->actorHandler = $actorHandler;
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

            // Force garbage collection between runs so we do not hit it while
            // running an executor which will fail the performance analysis.
            gc_collect_cycles();
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

    /**
     * @param \eZ\Publish\Profiler\Actor $actor
     * @throws \RuntimeException if no visitor for the visited actor class could be found
     * @return void
     */
    public function visitActor( Actor $actor )
    {
        if (!$this->actorHandler->canHandle($actor)) {
            throw new \RuntimeException("No actor handler found for: " . get_class( $actor ));
        }

        return $this->actorHandler->handle( $actor );
    }
}
