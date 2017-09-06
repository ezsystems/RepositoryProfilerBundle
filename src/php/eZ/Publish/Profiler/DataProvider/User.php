<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;

class User extends DataProvider
{
    public function get($languageCode)
    {
        return new \eZ\Publish\Core\FieldType\Author\Value(
            [
                new \eZ\Publish\Core\FieldType\Author\Author(['id' => 14]),
            ]
        );
    }
}
