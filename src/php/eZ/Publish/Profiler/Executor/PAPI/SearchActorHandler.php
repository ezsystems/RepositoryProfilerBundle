<?php

namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\API\Repository\SearchService;

use eZ\Publish\Profiler\ContentType;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;

class SearchActorHandler extends Handler
{
    /**
     * @var \eZ\Publish\Core\Repository\SearchService
     */
    private $searchService;

    /**
     * @param \eZ\Publish\Core\Repository\SearchService $searchService
     */
    public function __construct( SearchService $searchService ) {
        $this->searchService = $searchService;
    }

    /**
     * Can handle
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\Search;
    }

    /**
     * Handle
     *
     * @param Actor $actor
     * @return void
     */
    public function handle(Actor $actor)
    {
        $result = $this->searchService->findContent($actor->query);
    }
}
