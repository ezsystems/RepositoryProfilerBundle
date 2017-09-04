<?php

namespace eZ\Publish\Profiler\Actor\Handler;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;

class Aggregate extends Handler
{
    /**
     * Handlers.
     *
     * @var Handler[]
     */
    private $handlers = [];

    /**
     * @param Handler[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * Add handler.
     *
     * @param Handler $handler
     */
    public function addHandler(Handler $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Can handle.
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return true;
    }

    /**
     * Handle.
     *
     * @param Actor $actor
     */
    public function handle(Actor $actor)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($actor)) {
                return $handler->handle($actor);
            }
        }

        throw new \OutOfBoundsException(
            'No handle found for actor ' . get_class($actor)
        );
    }
}
