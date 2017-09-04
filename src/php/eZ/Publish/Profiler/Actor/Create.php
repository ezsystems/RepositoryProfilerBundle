<?php

namespace eZ\Publish\Profiler\Actor;

use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\GaussDistributor;
use eZ\Publish\Profiler\Storage;

class Create extends Actor
{
    public $type;

    public $count;

    public $parentLocationId = 2;

    public $storage;

    public function __construct($count, $type, $subActor = null, Storage $storage = null)
    {
        $this->count = $count;
        $this->subActor = $subActor;
        $this->type = $type;
        $this->storage = $storage ?: new Storage\NullStorage();
    }

    public function getFlatList()
    {
        $this->iterations =
            $this->count === 1 ?
            $this->count :
            GaussDistributor::getNumber($this->count);

        return parent::getFlatList();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Create Content';
    }
}
