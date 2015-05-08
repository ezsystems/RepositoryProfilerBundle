<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;

use eZ\Publish\API\Repository\Values\Content\Query;

class Search extends Actor
{
    public $name;

    public $query;

    public function __construct( $name, Query $query )
    {
        $this->name = $name;
        $this->query = $query;
    }
}

