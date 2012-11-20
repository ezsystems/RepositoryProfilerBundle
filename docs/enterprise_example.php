<?php

namespace eZ\Publish\Profiler;

// Makes the following variables available:
// - $repository (Access to Public API)
// - $persistenceHandler (Access to SPI (Persistence Handler))
// - $dbHandler (Direct access to the currently used database handler)
require __DIR__ . '/bootstrap.php';

$folderType = new ContentType(
    'folder',
    array(
        'title' => new Field\TextLine(),
    )
);

$articleType = new ContentType(
    'article',
    array(
        'title' => new Field\TextLine(),
        'body' => new Field\XmlText( new DataProvider\XmlText() ),
        'author' => new Field\Author( new DataProvider\User( 'editor' ) ),
        // …
    )
);

$commentType = new ContentType(
    'comment',
    array(
        'text' => new Field\TextBlock(),
        'author' => new Field\Author( new DataProvider\Aggregate( array(
            new DataProvider\AnonymousUser(),
            new DataProvider\User( 'user' )
        ) ) ),
        // …
    )
);

$createTask = new Task(
    new Actor\Create(
        1, $folderType,
        new Actor\Create(
            12, $folderType,
            new Actor\Create(
                50, $articleType,
                new Actor\Create(
                    5, $commentType
                ),
                $articles = new Storage\LimitedRandomized()
            )
        )
    )
);

$viewTask = new Task(
    new Actor\SubtreeView(
        $articles
    )
);

/* Not implemented yet:
$searchTask = new Task(
    new Actor\Search( array(
        'foo',
        'bar'
    ) )
); // */

$logger = new Logger\Statistics();
$executor = new Executor\SPI(
    $persistenceHandler,
    $repository->getFieldTypeService(),
    array(
        new Constraint\Ratio( $createTask, 1 ),
    ),
    $logger,
    new Aborter\ContentObjectAttributeCount(
        $dbHandler,
        1000
    )
);
$executor->run();

$articles->reset();
$executor = new Executor\PAPI(
    $repository,
    array(
        new Constraint\Ratio( $createTask, 1/5 ),
        new Constraint\Ratio( $viewTask, 1 ),
    ),
    $logger,
    new Aborter\ContentObjectAttributeCount(
        $dbHandler,
        2000
    )
);
$executor->run();

$logger->showSummary();

