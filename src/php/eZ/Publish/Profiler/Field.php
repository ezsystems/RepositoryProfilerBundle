<?php

namespace eZ\Publish\Profiler;

abstract class Field
{
    public $dataProvider;

    public $searchable = true;

    public $translatable = true;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Get type identifier.
     *
     * @return string
     */
    abstract public function getTypeIdentifier();
}
