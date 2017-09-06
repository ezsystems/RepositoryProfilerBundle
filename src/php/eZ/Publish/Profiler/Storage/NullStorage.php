<?php

namespace eZ\Publish\Profiler\Storage;

use eZ\Publish\Profiler\Storage;

class NullStorage extends Storage
{
    public function store($object)
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

    public function pull()
    {
        // Just du nothing…
    }
}
