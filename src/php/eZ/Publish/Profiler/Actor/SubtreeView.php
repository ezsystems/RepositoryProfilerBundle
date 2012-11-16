<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\ContentObjectStorage;

class SubtreeView extends Actor
{
    public $storage;

    public function __construct( ContentObjectStorage $storage )
    {
        $this->storage = $storage;
    }
}

