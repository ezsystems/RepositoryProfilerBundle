<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;

class XmlText extends DataProvider
{
    public function get()
    {
        $doc = new \DOMDocument();
        $doc->loadXml( trim( '
            <?xml version="1.0" ?>
            <document/>
        ' ) );

        return new \eZ\Publish\Core\FieldType\XmlText\Value( $doc );
    }
}

