<?php

namespace eZ\Publish\Profiler\Logger;

use eZ\Publish\Profiler\Logger;
use eZ\Publish\Profiler\Executor;
use eZ\Publish\Profiler\Actor;

class Statistics extends Logger
{
    protected $executor = [];

    protected $currentExecutor = 0;

    protected $actor = [];

    protected $currentActor = 0;

    public function startExecutor(Executor $executor)
    {
        ++$this->currentExecutor;
        $this->executor[$this->currentExecutor]['name'] = get_class($executor);
        $this->executor[$this->currentExecutor]['start'] = microtime(true);

        echo 'Start ', get_class($executor), ': ', PHP_EOL;
    }

    public function stopExecutor(Executor $executor)
    {
        $this->executor[$this->currentExecutor]['end'] = microtime(true);
        $this->currentActor = 0;

        echo PHP_EOL, PHP_EOL;
    }

    public function startActor(Actor $actor)
    {
        ++$this->currentActor;
        $this->actor[$this->currentExecutor][$actor->getName()][$this->currentActor]['start'] = microtime(true);

        // Just do nothing…
    }

    public function stopActor(Actor $actor)
    {
        $this->actor[$this->currentExecutor][$actor->getName()][$this->currentActor]['end'] = microtime(true);

        echo "    \r {$this->currentExecutor}: {$this->currentActor}";
    }

    public function showSummary()
    {
        foreach ($this->executor as $executor => $stats) {
            printf(
                '%s: %.2fs:' . PHP_EOL,
                $stats['name'],
                $runtime = $stats['end'] - $stats['start']
            );

            foreach ($this->actor[$executor] as $type => $stats) {
                $statistics = $this->getStatistics(
                    array_map(
                        function ($stats) {
                            return ($stats['end'] - $stats['start']) * 1000;
                        },
                        $stats
                    ),
                    $runtime
                );

                printf(' * %s: %d (%.2f per second)' . PHP_EOL, $type, $statistics['count'], $statistics['per_second']);
                printf('   - Minimum.......: %.2f ms' . PHP_EOL, $statistics['minimum']);
                printf('   - 90%% Percentile: %.2f ms' . PHP_EOL, $statistics['90']);
                printf('   - Median........: %.2f ms' . PHP_EOL, $statistics['median']);
                printf('   - Average.......: %.2f ms' . PHP_EOL, $statistics['average']);
                printf('   - Maximum.......: %.2f ms' . PHP_EOL, $statistics['maximum']);
                printf('   - Std. Deviation: %.2f ms' . PHP_EOL, $statistics['std_deviation']);
            }
        }
    }

    /**
     * Get statistics.
     *
     * @param array $runtimes
     * @return array
     */
    protected function getStatistics(array $runtimes, $runtime)
    {
        sort($runtimes);

        $count = count($runtimes);
        $sum = array_sum($runtimes);
        $average = $sum / $count;

        $variance = 0;
        foreach ($runtimes as $duration) {
            $variance += pow($duration - $average, 2) / $count;
        }

        return [
            'count' => $count,
            'sum' => $sum,
            'per_second' => $count / $runtime,
            'minimum' => reset($runtimes),
            'maximum' => end($runtimes),
            'average' => $average,
            '90' => array_sum(array_slice($runtimes, 0, $c90 = ceil($count * .9))) / $c90,
            'median' => $runtimes[floor($count / 2)],
            'variance' => $variance,
            'std_deviation' => sqrt($variance),
        ];
    }
}
