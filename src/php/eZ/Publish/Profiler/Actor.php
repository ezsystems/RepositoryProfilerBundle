<?php

namespace eZ\Publish\Profiler;

abstract class Actor
{
    public $subActor = null;

    protected $iterations = 1;

    public function getFlatList()
    {
        $list = array();
        for ( $i = 0; $i < $this->iterations; ++$i )
        {
            $list[] = $this;

            if ( $this->subActor !== null )
            {
                $list = array_merge(
                    $list,
                    $this->subActor->getFlatList()
                );
            }
        }

        return $list;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return get_class($this);
    }
}

