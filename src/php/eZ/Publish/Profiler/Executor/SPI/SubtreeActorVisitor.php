<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;

use eZ\Publish\SPI\Persistence;
use eZ\Publish\API\Repository\Values\Content\Query;

class SubtreeActorVisitor
{
    protected $handler;

    public function __construct( Persistence\Handler $handler )
    {
        $this->handler = $handler;
    }

    public function visit( Actor\SubtreeView $actor )
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
        $searchHandler = $this->handler->searchHandler();
        $result = $searchHandler->findContent(
            new Query( array(
                'criterion' => new Query\Criterion\Subtree( $location->pathString )
            ) )
        );
    }
}

