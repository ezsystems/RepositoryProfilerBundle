<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Storage;

class SubtreeCopy extends Actor
{
    /**
     * @var \eZ\Publish\Profiler\Storage
     */
    public $sourceStorage;

    /**
     * @var \eZ\Publish\Profiler\Storage
     */
    public $targetStorage;

    public function __construct(Storage $sourceStorage, Storage $targetStorage)
    {
        $this->sourceStorage = $sourceStorage;
        $this->targetStorage = $targetStorage;
    }

    public function getName()
    {
        return 'Copy Subtree';
    }
}
