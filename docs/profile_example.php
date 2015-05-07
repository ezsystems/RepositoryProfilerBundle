<?php

namespace eZ\Publish\Profiler;

use eZ\Publish\API\Repository\Values\Content\Query;

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

$simpleSearchTask = new Task(
    new Actor\Search(
        new Query( array(
            'query' => new Query\Criterion\Field(
                'title',
                Query\Criterion\Operator::EQ,
                'test'
            ),
        ) )
    )
);

$sortedSearchTask = new Task(
    new Actor\Search(
        new Query( array(
            'query' => new Query\Criterion\ContentTypeId(
                Query\Criterion\Operator::GT,
                0
            ),
            'sortClauses' => array( new Query\SortClause\Field(
                'profiler-article',
                'title',
                Query::SORT_ASC,
                'eng-US'
            ) ),
        ) )
    )
);

// Current executor – provided by the caller
/*
$executor->run(
    array(
        new Constraint\Ratio( $createTask, 1 ),
    ),
    new Aborter\Count(50)
); // */

$executor->run(
    array(
        new Constraint\Ratio( $createTask, 1/10 ),
        new Constraint\Ratio( $viewTask, 1 ),
        new Constraint\Ratio( $simpleSearchTask, 1/3 ),
        new Constraint\Ratio( $sortedSearchTask, 1/5 ),
    ),
    new Aborter\Count(200)
);
