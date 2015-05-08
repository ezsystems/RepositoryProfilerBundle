<?php

namespace eZ\Publish\Profiler\Field;

use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\DataProvider;

class Author extends Field
{
    public $translatable = false;

    public function __construct( DataProvider $dataProvider = null )
    {
        parent::__construct( $dataProvider ?: new DataProvider\AnonymousUser() );
    }

    /**
     * Get type identifier
     *
     * @return string
     */
    public function getTypeIdentifier()
    {
        return 'ezauthor';
    }
}

