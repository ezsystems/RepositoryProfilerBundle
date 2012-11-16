<?php

namespace eZ\Publish\Profiler;

class GaussDistributor
{
    public static function getNumber( $center, $width = null )
    {
        // See: https://en.wikipedia.org/wiki/Normal_distribution#Generating_values_from_normal_distribution
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();

        $x = sqrt( -2 * log( $u ) ) * cos( 2 * M_PI * $v );

        $width = $width ?: $center / 4;
        return max( 0, (int) floor( ( $x * $width ) + $center ) );
    }
}

