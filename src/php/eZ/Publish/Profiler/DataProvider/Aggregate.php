<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;

class Aggregate extends DataProvider
{
    protected $dataProviders;

    public function __construct(array $dataProviders)
    {
        foreach ($dataProviders as $dataProvider) {
            $this->addDataProvider($dataProvider);
        }
    }

    public function addDataProvider(DataProvider $dataProvider)
    {
        $this->dataProviders[] = $dataProvider;
    }

    public function get($languageCode)
    {
        $key = array_rand($this->dataProviders);

        return $this->dataProviders[$key]->get($languageCode);
    }
}
