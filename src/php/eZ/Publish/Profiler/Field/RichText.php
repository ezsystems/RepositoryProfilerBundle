<?php

namespace eZ\Publish\Profiler\Field;

use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\DataProvider;

class RichText extends Field
{
    public function __construct(DataProvider $dataProvider = null)
    {
        parent::__construct($dataProvider ?: new DataProvider\RichText());
    }

    /**
     * Get type identifier.
     *
     * @return string
     */
    public function getTypeIdentifier()
    {
        return 'ezrichtext';
    }
}
