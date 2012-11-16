<?php

namespace eZ\Publish\Profiler;

class Task
{
    public $actor;

    protected $actorList = null;

    public function __construct( Actor $actor )
    {
        $this->actor = $actor;
    }

    public function getNext()
    {
        if ( $this->actorList === null )
        {
            $this->initializeActorList();
            return reset( $this->actorList );
        }

        $next = next( $this->actorList );
        if ( $next === false )
        {
            $this->initializeActorList();
            return reset( $this->actorList );
        }

        return $next;
    }

    protected function initializeActorList()
    {
        $this->actorList = $this->actor->getFlatList();
    }
}

