<?php

function parseLoadLog( $logFile, $resolution = 10 )
{
    fwrite(STDERR, "Open Load Log ({$logFile})\n");
    $log = array();
    $fp  = fopen( $logFile, 'r' );

    $last     = null;
    $lastTime = null;
    do {
        $line = fgets( $fp );

        if ( empty( $line ) )
        {
            continue;
        }

        list( $date, $data ) = explode( ';', trim( $line ) );
        $date = new DateTime( trim( $date ) );
        $time = (int) $date->format( 'U' );

        if ( $time < ( $lastTime + $resolution ) )
        {
            continue;
        }

        preg_match_all( '((?P<value>\\d+)\\s*(?P<type>\\D+))', trim( $data ), $matches );
        $matches['type'] = array_map( 'trim', $matches['type'] );
        $data = array_combine( $matches['type'], $matches['value'] );

        if ( $last !== null )
        {
            foreach ( $data as $name => $value )
            {
                if ( strpos( $name, 'memory' ) || strpos( $name, 'swap' ) )
                {
                    $log[$time][$name] = $value;
                }
                else
                {
                    $log[$time][$name] = $value - $last[$name];
                }
            }
        }

        $last     = $data;
        $lastTime = $time;
    } while ( !feof( $fp ) );

    return $log;
}


function averageOfPercent(array $data, $percent)
{
    $length = ceil(count($data) * $percent);

    if (0 == $length) {
        return 0;
    }

    sort($data);

    return $data[$length - 1];
}
