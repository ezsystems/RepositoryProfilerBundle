<?php

namespace eZ\Publish\Profiler;

class ContentType
{
    public $name;

    public $fields;

    public $languageCodes;

    public $versionCount;

    public function __construct($name, $fields, array $languageCodes, $versionCount = 1)
    {
        $this->name = $name;
        $this->fields = $fields;
        $this->languageCodes = $languageCodes;
        $this->versionCount = $versionCount;
    }
}
