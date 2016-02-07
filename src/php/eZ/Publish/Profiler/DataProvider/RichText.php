<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;

class RichText extends DataProvider
{
    public function get($languageCode)
    {
        $doc = new \DOMDocument();
        $doc->loadXml( trim( '
            <?xml version="1.0" encoding="UTF-8"?>
            <document/>
        ' ) );

        return new \eZ\Publish\Core\FieldType\RichText\Value( $doc );
    }
}

