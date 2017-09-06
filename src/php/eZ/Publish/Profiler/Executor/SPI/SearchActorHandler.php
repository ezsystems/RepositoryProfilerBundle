<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;
use eZ\Publish\SPI\Search;

class SearchActorHandler extends Handler
{
    protected $searchHandler;

    public function __construct(Search\Handler $searchHandler)
    {
        $this->searchHandler = $searchHandler;
    }

    /**
     * Can handle.
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\Search;
    }

    /**
     * Handle.
     *
     * @param Actor $actor
     */
    public function handle(Actor $actor)
    {
        $result = $this->searchHandler->findContent($actor->query);
    }
}
