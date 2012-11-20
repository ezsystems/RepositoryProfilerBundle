<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Storage;

class SubtreeView extends Actor
{
    public $storage;

    public function __construct( Storage $storage )
    {
        $this->storage = $storage;
    }
}

