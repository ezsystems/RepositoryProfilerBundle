<?php

namespace eZ\Publish\Profiler;

abstract class ContentObjectStorage
{
    abstract public function store( $object );

    abstract public function get();

    abstract public function reset();
}

