<?php

namespace eZ\Publish\Profiler\Field;

use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\DataProvider;

class TextBlock extends Field
{
    public $searchable = false;

    public function __construct(DataProvider $dataProvider = null)
    {
        parent::__construct($dataProvider ?: new DataProvider\Text());
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
