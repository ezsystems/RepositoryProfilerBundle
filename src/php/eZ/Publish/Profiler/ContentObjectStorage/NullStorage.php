<?php

namespace eZ\Publish\Profiler\ContentObjectStorage;

use eZ\Publish\Profiler\ContentObjectStorage;

class NullStorage extends ContentObjectStorage
{
    public function store( $object )
    {
        // Just du nothing…
    }

    public function get()
    {
        // Just du nothing…
    }

    public function reset()
    {
        // Just du nothing…
    }
}

