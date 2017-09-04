<?php

namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler as ActorHandler;
use eZ\Publish\Profiler\Actor\Handler\Exception\ActorHandlerException;
use eZ\Publish\Profiler\Actor\SubtreeCopy;
use eZ\Publish\SPI\Persistence\Handler;

class SubtreeCopyActorHandler extends ActorHandler
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $handler;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function canHandle(Actor $actor)
    {
        return $actor instanceof SubtreeCopy;
    }

    public function handle(Actor $actor)
    {
        /** @var \eZ\Publish\Core\Repository\Values\Content\Content $source */
        $source = $actor->sourceStorage->pull();
        if (!$source) {
            // If no content objects have been stored yet there is nothing to be copy
            return;
        }

        /** @var \eZ\Publish\Core\Repository\Values\Content\Content $target */
        $target = $actor->targetStorage->get();
        if (!$target) {
            throw new ActorHandlerException($actor, 'Undefined target parent location for the copy operation');
        }

        $locationHandler = $this->handler->locationHandler();
        $locationHandler->copySubtree(
            $source->contentInfo->mainLocationId,
            $target->contentInfo->mainLocationId
        );
    }
}
