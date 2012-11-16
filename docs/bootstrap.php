<?php

require __DIR__ . '/../src/php/eZ/Publish/Profiler/bootstrap.php';

if ( extension_loaded( 'xdebug' ) )
{
    echo "Performance Tests do not work xdebug.", PHP_EOL;
    exit( 1 );
}

$setupFactory = new \eZ\Publish\API\Repository\Tests\SetupFactory\Legacy();
$repository = $setupFactory->getRepository( true );

$persistenceHandlerProperty = new \ReflectionProperty( get_class( $repository ), 'persistenceHandler' );
$persistenceHandlerProperty->setAccessible( true );
$persistenceHandler = $persistenceHandlerProperty->getValue( $repository );

$dbHandlerProperty = new \ReflectionProperty( get_class( $persistenceHandler ), 'dbHandler' );
$dbHandlerProperty->setAccessible( true );
$dbHandler = $dbHandlerProperty->getValue( $persistenceHandler );

