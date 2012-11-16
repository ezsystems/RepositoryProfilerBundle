<?php

namespace eZ\Publish\Profiler;

abstract class Field
{
    public $dataProvider;

    public function __construct( DataProvider $dataProvider )
    {
        $this->dataProvider = $dataProvider;
    }
}

