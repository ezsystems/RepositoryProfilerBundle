===============
Profiler Bundle
===============

Bundle to profile eZ Platform installations and setup scenarios to be able to
continuously test to keep track of performance regressions of repository and
underlying storage engines(s) 

This bundle contains two means of profiling your eZ Publish stack.

* API Profiler

  The API profiler executes tests against the Public API or directly against
  the SPI. It is capable of executing different scenarios.

* jMeter Tests

  The jMeter tests run tests against the HTTP frontend. Currently just a random
  browser is implement. This is most useful together with some profiling done
  in the background to detect the actual bottlenecks.

------------
API Profiler
------------

Warning
=======

    Running the performance tests / profiling will chnage the contents of your
    database. Use with care.

Usage
=====

Install the bundle inside of an existing ez-platform installation::

    composer.phar require ezsystems/profiler-bundle dev-master

Then you can run the performance tests using::

    php ezpublish/console profiler:run papi vendor/ezsystems/profiler-bundle/docs/profile_example.php

The provided file specifies the performance test you want to run. The file
mentioned here is an example file provided with the bundle. You can run the
tests either against the Public API (``papi``) or directly against the SPI
(``spi``).

Configuration
=============

To model different scenarios then the on provided in the example file is a
little more complex.

Types
-----

First we define multiple content types. The content type definitions are
simpler then in the APIs to test, but are mapped accordingly::

    $articleType = new ContentType(
        'article',
        array(
            'title' => new Field\TextLine(),
            'body' => new Field\XmlText( new DataProvider\XmlText() ),
            'author' => new Field\Author( new DataProvider\User( 'editor' ) ),
            // …
        ),
        array($defaultLanguage, 'ger-DE', 'fra-FR'), // Languages of content
        8 // Average number of versions
    );

First we define the name of the type and then its fields. Each field should
have a data provider assigned, which provides random test data.

Optionally we can define multiple languages in which content will be created.
Also optionally an average number of versions can be defined to "age" content.
You can define as many types as sensible.

Actors
------

Actors actually do something with the defined types. There are currently three
different actors, but you could define more:

* ``Actor\Create``

  Creates content structures. You can stack multiple ``Create`` actors to
  create deep content structures::

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

  This example will create a structure of folder types, which, in the end, will
  contain articles, which will contain comments. The specified numbers are the
  average number of children which are created.

  You may optionally specify an object store, if you want to reference some of
  the created content objects in a different actor, like the next one.

* ``Actor\SubtreeView``

  This actors simulates an eZ Platform view operation of a content object by
  executing similar queries to the content repository::

    $viewTask = new Task(
        new Actor\SubtreeView(
            $articles
        )
    );

  You should provide the actor with an object store so it can pick from a
  number of existing content objects which would be viewed by users of an
  application.

* ``Actor\Search``

  This actor just executes a search. Searches are specified as in the Public
  API or the SPI using a common ``Query`` object.

Execution
---------

Finally we want to execute our configured scenario consisting of types and
actors. For this an executor is used::

    $executor->run(
        array(
            new Constraint\Ratio( $createTask, 1/10 ),
            new Constraint\Ratio( $viewTask, 1 ),
            new Constraint\Ratio( $simpleSearchTask, 1/3 ),
            new Constraint\Ratio( $sortedSearchTask, 1/5 ),
        ),
        new Aborter\Count(200)
    );

The executor will be provided with an array of ``Constraint`` objects each
associated with a task. In this case ``Constraint\Ratio`` objects are used,
which will only execute a task according to the given probability.

The Aborter defines when the execution will be halted. It could also check for
the amount of create content objects or just abort after a given time span. The
``Count`` aborter just aborts after the given number of iterations.

------------
jMeter Tests
------------

Usage
=====

The jMeter test can be run by just executing ``ant`` in the root directory. In
the first run jMeter will be downloaded. In subsequent runs the already
downloaded files will be used. Ant 1.8 is required to run the example.

The test hits the configured host and will create files providing you with
statistics about the run:

* ``build/result.jtl``

  jMeter log file for further analysis

* ``build/result.csv``

  Simple grouping of response times by URL

Configuration
=============

You can configure the run by creating a file ``jmeter.properties.local`` to
overwrite the variables in the ``jmeter.properties`` file. You definitely want
to adapt the ``jmeter.server`` in there to point to the website you want to put
under test. All options are documented in the ``jmeter.properties`` file.


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
