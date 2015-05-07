<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;

use eZ\Publish\SPI\Persistence;
use eZ\Publish\SPI\Search;
use eZ\Publish\API\Repository\Values\Content\Query;

class SubtreeActorHandler extends Handler
{
    protected $handler;

    protected $searchHandler;

    public function __construct( Persistence\Handler $handler, Search\Content\Handler $searchHandler )
    {
        $this->handler = $handler;
        $this->searchHandler = $searchHandler;
    }

    /**
     * Can handle
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\SubtreeView;
    }

    /**
     * Handle
     *
     * @param Actor $actor
     * @return void
     */
    public function handle(Actor $actor)
    {
        if ( !$object = $actor->storage->get() )
        {
            // There are no content objects yet, we ignore this.
            return;
        }

        // Load content for the object itself
        $contentHandler = $this->handler->contentHandler();
        $contentHandler->load( $object->versionInfo->contentInfo->id, $object->versionInfo->versionNo );

        $locationHandler = $this->handler->locationHandler();
        $location = $locationHandler->load( $object->versionInfo->contentInfo->mainLocationId );

        // Select all content below content
        $result = $this->searchHandler->findContent(
            new Query( array(
                'criterion' => new Query\Criterion\Subtree( $location->pathString )
            ) )
        );
    }
}

