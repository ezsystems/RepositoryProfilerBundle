<?php
namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\LocationService;
use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\Profiler\Actor;

class SubtreeActorVisitor
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
     * @var \eZ\Publish\Core\Repository\SearchService
     */
    private $searchService;

    /**
     * @param \eZ\Publish\Core\Repository\ContentService $contentService
     * @param \eZ\Publish\Core\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\Repository\SearchService $searchService
     */
    public function __construct( ContentService $contentService, LocationService $locationService, SearchService $searchService ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
    }

    /**
     * @param \eZ\Publish\Profiler\Actor\SubtreeView $actor
     */
    public function visit( Actor\SubtreeView $actor )
    {

        /** @var \eZ\Publish\Core\Repository\Values\Content\Content $content */
        $content = $actor->storage->get();


        if (!$content) {
            // If no content objects have been stored yet there is nothing to be read
            return;
        }

        // Eventhough we already have our object from the cache we want to simulate a read here
        $content = $this->contentService->loadContent($content->contentInfo->id);

        $locations = $this->locationService->loadLocations(
            $content->contentInfo
        );

        $result = $this->searchService->findContent(
            new Query(
                array(
                    'criterion' => new Criterion\Subtree( $locations[0]->pathString )
                )
            )
        );
    }
}