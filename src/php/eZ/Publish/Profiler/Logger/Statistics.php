<?php

namespace eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Actor;

class Statistics extends Logger
{
    protected $executor = array();

    protected $currentExecutor = 0;

    protected $actor = array();

    protected $currentActor = 0;

    public function startExecutor( Executor $executor )
    {
        $this->currentExecutor++;
        $this->executor[$this->currentExecutor]['name'] = get_class( $executor );
        $this->executor[$this->currentExecutor]['start'] = microtime( true );

        echo "Start ", get_class( $executor ), ': ', PHP_EOL;
    }

    public function stopExecutor( Executor $executor )
    {
        $this->executor[$this->currentExecutor]['end'] = microtime( true );
        $this->currentActor = 0;

        echo PHP_EOL, PHP_EOL;
    }

    public function startActor( Actor $actor )
    {
        $this->currentActor++;
        $this->actor[$this->currentExecutor][get_class( $actor )][$this->currentActor]['start'] = microtime( true );

        // Just do nothing…
    }

    public function stopActor( Actor $actor )
    {
        $this->actor[$this->currentExecutor][get_class( $actor )][$this->currentActor]['end'] = microtime( true );

        echo "    \r {$this->currentExecutor}: {$this->currentActor}";
    }

    public function showSummary()
    {
        foreach ( $this->executor as $executor => $stats )
        {
            printf(
                "%s: %.2fs:" . PHP_EOL,
                $stats['name'],
                $runtime = $stats['end'] - $stats['start']
            );

            foreach ( $this->actor[$executor] as $type => $stats )
            {
                printf(
                    " * %s: %d (%.2f per second)" . PHP_EOL,
                    $type,
                    count( $stats ),
                    count( $stats ) / $runtime
                );

                $runtimes = array_map(
                    function ( $stats )
                    {
                        return ( $stats['end'] - $stats['start'] ) * 1000;
                    },
                    $stats
                );

                printf(
                    "   - Minimum: %.2f ms" . PHP_EOL,
                    min( $runtimes )
                );
                printf(
                    "   - Average: %.2f ms" . PHP_EOL,
                    array_sum( $runtimes ) / count( $runtimes )
                );
                printf(
                    "   - Maximum: %.2f ms" . PHP_EOL,
                    max( $runtimes )
                );
            }

        }
    }
}

