<?php

namespace eZ\Publish\Profiler\Actor\Handler\Exception;

use eZ\Publish\Profiler\Actor;
use RuntimeException;
use Exception;

class ActorHandlerException extends RuntimeException
{
    /**
     * @var \eZ\Publish\Profiler\Actor
     */
    protected $actor;

    public function __construct(Actor $actor, $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Error occurred while executing %s: %s', get_class($actor), $message), $code, $previous);

        $this->actor = $actor;
    }

    /**
     * Gets the Actor related to Exception.
     *
     * @return \eZ\Publish\Profiler\Actor
     */
    public function getActor()
    {
        return $this->actor;
    }
}
