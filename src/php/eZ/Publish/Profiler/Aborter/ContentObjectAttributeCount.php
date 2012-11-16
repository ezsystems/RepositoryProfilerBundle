<?php

namespace eZ\Publish\Profiler\Aborter;

use eZ\Publish\Profiler\Aborter;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

class ContentObjectAttributeCount extends Aborter
{
    protected $dbHandler;

    protected $attributeCount;

    public function __construct( EzcDbHandler $dbHandler, $attributeCount )
    {
        $this->dbHandler = $dbHandler;
        $this->attributeCount = $attributeCount;
    }

    public function shouldAbort()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select( 'COUNT(*)' )
            ->from( 'ezcontentobject_attribute' );
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll( \PDO::FETCH_COLUMN );

        return $result[0] >= $this->attributeCount;
    }
}

