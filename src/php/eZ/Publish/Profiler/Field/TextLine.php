<?php

namespace eZ\Publish\Profiler\Field;

use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\DataProvider;

class TextLine extends Field
{
    public function __construct(DataProvider $dataProvider = null)
    {
        parent::__construct($dataProvider ?: new DataProvider\StringProvider());
    }

    /**
     * Get type identifier.
     *
     * @return string
     */
    public function getTypeIdentifier()
    {
        return 'ezstring';
    }
}
