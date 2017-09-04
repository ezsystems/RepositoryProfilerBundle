<?php

namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler as ActorHandler;
use eZ\Publish\Profiler\Actor\Handler\Exception\ActorHandlerException;

class SubtreeCopyActorHandler extends ActorHandler
{
    /**
     * @var \eZ\Publish\Core\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\Repository\LocationService
     */
    private $locationService;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(ContentService $contentService, LocationService $locationService)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\SubtreeCopy;
    }

    public function handle(Actor $actor)
    {
        /** @var \eZ\Publish\Core\Repository\Values\Content\Content $source */
        $source = $actor->sourceStorage->pull();
        if (!$source) {
            // If no content objects have been stored yet there is nothing to be copy
            return;
        }

        $sourceLocation = $this->loadLocation($source);

        /** @var \eZ\Publish\Core\Repository\Values\Content\Content $target */
        $target = $actor->targetStorage->get();
        if (!$target) {
            throw new ActorHandlerException($actor, 'Undefined target parent location for the copy operation');
        }
        $targetLocation = $this->loadLocation($target);

        $this->locationService->copySubtree($sourceLocation, $targetLocation);
    }

    private function loadLocation(Content $content)
    {
        // Eventhough we already have our object from the cache we want to simulate a read here
        $content = $this->contentService->loadContent(
            $content->contentInfo->id
        );

        return $this->locationService->loadLocation(
            $content->contentInfo->mainLocationId
        );
    }
}
