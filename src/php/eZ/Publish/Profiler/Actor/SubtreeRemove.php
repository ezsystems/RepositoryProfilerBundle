<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Storage;

class SubtreeRemove extends Actor
{
    public $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Remove Subtree';
    }
}
