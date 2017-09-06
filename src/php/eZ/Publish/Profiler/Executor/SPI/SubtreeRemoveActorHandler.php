<?php
namespace eZ\Publish\Profiler\Executor\SPI;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;
use eZ\Publish\SPI\Persistence;

class SubtreeRemoveActorHandler extends Handler
{
    protected $handler;

    public function __construct(Persistence\Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Can handle.
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\SubtreeRemove;
    }

    /**
     * Handle.
     *
     * @param Actor $actor
     */
    public function handle(Actor $actor)
    {
        $object = $actor->storage->pull();

        if (!$object) {
            // If no content objects have been stored yet there is nothing to be read
            return;
        }

        $locationHandler = $this->handler->locationHandler();
        $location = $locationHandler->removeSubtree($object->versionInfo->contentInfo->mainLocationId);
    }
}
