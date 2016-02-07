<?php

namespace eZ\Publish\Profiler\DataProvider;

use eZ\Publish\Profiler\DataProvider;
use Faker;

class StringProvider extends DataProvider
{
    public function get($languageCode)
    {
        $faker = Faker\Factory::create();
        return $faker->catchPhrase;
    }
}

