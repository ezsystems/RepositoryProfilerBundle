<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;
use eZ\Publish\Profiler\GaussDistributor;
use Faker;

class Text extends DataProvider
{
    public function get($languageCode)
    {
        $faker = Faker\Factory::create();

        return $faker->text(GaussDistributor::getNumber(2048));
    }
}
