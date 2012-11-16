<?php

namespace eZ\Publish\Profiler;

class ContentType
{
    public $name;

    public $fields;

    public function __construct( $name, $fields )
    {
        $this->name = $name;
        $this->fields = $fields;
    }
}

