<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;

use eZ\Publish\API\Repository\Values\Content\Query;

class Search extends Actor
{
    public $query;

    public function __construct( Query $query )
    {
        $this->query = $query;
    }
}

