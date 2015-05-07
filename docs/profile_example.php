<?php

namespace eZ\Publish\Profiler;

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

// Current executor – provided by the caller
$executor->run(
    array(
        new Constraint\Ratio( $createTask, 1/5 ),
        new Constraint\Ratio( $viewTask, 1 ),
    ),
    new Aborter\Count(10)
);
