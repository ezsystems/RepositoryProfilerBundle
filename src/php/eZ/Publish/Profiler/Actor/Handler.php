<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;

abstract class Handler
{
    /**
     * Can handle.
     *
     * @param Actor $actor
     * @return bool
     */
    abstract public function canHandle(Actor $actor);

    /**
     * Handle.
     *
     * @param Actor $actor
     *
     * @throws \eZ\Publish\Profiler\Actor\Handler\Exception\ActorHandlerException
     */
    abstract public function handle(Actor $actor);
}
