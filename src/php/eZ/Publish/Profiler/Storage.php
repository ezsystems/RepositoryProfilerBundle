<?php

namespace eZ\Publish\Profiler;

abstract class Storage
{
    abstract public function store( $object );

    abstract public function get();

    abstract public function reset();
}

