<?php

namespace eZ\Publish\Profiler;

abstract class DataProvider
{
    abstract public function get($languageCode);
}
