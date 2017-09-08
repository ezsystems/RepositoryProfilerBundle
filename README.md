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

API Profiler
------------

### Warning

    Running the performance tests / profiling will chnage the contents of your
    database. Use with care.

### Usage

Install the bundle inside of an existing ez-platform installation::

    composer.phar require ezsystems/profiler-bundle dev-master

Enable Bundle in kernel by adding:

```php
   new eZ\Publish\ProfilerBundle\EzPublishProfilerBundle(),
```

Then you can run the performance tests using::

    php app/console profiler:run papi vendor/ezsystems/profiler-bundle/docs/profile_example.php

The provided file specifies the performance test you want to run. The file
mentioned here is an example file provided with the bundle. You can run the
tests either against the Public API (``papi``) or directly against the SPI
(``spi``).

### Configuration

To model different scenarios then the on provided in the example file is a
little more complex.

#### Types

First we define multiple content types. The content type definitions are
simpler then in the APIs to test, but are mapped accordingly::

```php
    $articleType = new ContentType(
        'article',
        [
            'title' => new Field\TextLine(),
            'body' => new Field\XmlText( new DataProvider\XmlText() ),
            'author' => new Field\Author( new DataProvider\User( 'editor' ) ),
            // …
        ],
        [$defaultLanguage, 'ger-DE', 'fra-FR'], // Languages of content
        8 // Average number of versions
    );
```

First we define the name of the type and then its fields. Each field should
have a data provider assigned, which provides random test data.

Optionally we can define multiple languages in which content will be created.
Also optionally an average number of versions can be defined to "age" content.
You can define as many types as sensible.

#### Actors

Actors actually do something with the defined types. There are currently three
different actors, but you could define more:

* ``Actor\Create``

  Creates content structures. You can stack multiple ``Create`` actors to
  create deep content structures::

```php
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
```

  This example will create a structure of folder types, which, in the end, will
  contain articles, which will contain comments. The specified numbers are the
  average number of children which are created.

  You may optionally specify an object store, if you want to reference some of
  the created content objects in a different actor, like the next one.

* ``Actor\SubtreeView``

  This actors simulates an eZ Platform view operation of a content object by
  executing similar queries to the content repository::

```php
    $viewTask = new Task(
        new Actor\SubtreeView(
            $articles
        )
    );
```

  You should provide the actor with an object store so it can pick from a
  number of existing content objects which would be viewed by users of an
  application.

* ``Actor\SubtreeCopy``

  This actor executes a subtree copy operation. It's require two object stores: 
  1st one with a nodes to copy and 2nd one with destination location. 
  
```php
$copyTask = new Task(
   new Actor\SubtreeCopy(
    $articles, $folder
   )
);
```  

* ``Actor\Search``

  This actor just executes a search. Searches are specified as in the Public
  API or the SPI using a common ``Query`` object.

#### Execution

Finally we want to execute our configured scenario consisting of types and
actors. For this an executor is used::

```php
    $executor->run(
        array(
            new Constraint\Ratio( $createTask, 1/10 ),
            new Constraint\Ratio( $viewTask, 1 ),
            new Constraint\Ratio( $simpleSearchTask, 1/3 ),
            new Constraint\Ratio( $sortedSearchTask, 1/5 ),
        ),
        new Aborter\Count(200)
    );
```

The executor will be provided with an array of ``Constraint`` objects each
associated with a task. In this case ``Constraint\Ratio`` objects are used,
which will only execute a task according to the given probability.

The Aborter defines when the execution will be halted. It could also check for
the amount of create content objects or just abort after a given time span. The
``Count`` aborter just aborts after the given number of iterations.

You might, like done in the example, define multiple executors which then will
be executed subsequently.

jMeter Tests
------------

### Usage

The jMeter test can be run by just executing ``ant`` in the root directory. In
the first run jMeter will be downloaded. In subsequent runs the already
downloaded files will be used. Ant 1.8 is required to run the example.

The test hits the configured host and will create files providing you with
statistics about the run:

* ``build/result.jtl``

  jMeter log file for further analysis

* ``build/result.csv``

  Simple grouping of response times by URL

### Configuration

You can configure the run by creating a file ``jmeter.properties.local`` to
overwrite the variables in the ``jmeter.properties`` file. You definitely want
to adapt the ``jmeter.server`` in there to point to the website you want to put
under test. All options are documented in the ``jmeter.properties`` file.

The implemented "Random Browser" only executes ``GET`` requests accessing
random links starting at the configured start page. It will not log in or
submit any forms (searches).

There are two options defining the behaviour of the random surfer:

* ``crawler.usertype.a.breadth``
  
  On average, how many links are clicked on the same page. Causes the user to
  click more links on the start page and the subsequent pages. (Default: 2)

* ``crawler.usertype.a.depth``

  On average, how deep a user will click through the website. Causes the user
  to follow links deeper into the website structure. (Default: 3)

Another important configuration is the ``jmeter.users`` value. It defines how
many users will access / surf the website in parallel. The default of 5 means
that 5 users will simultaneously surf on the website. With the configured
timings that means something between 1 Req/s and 2 Req/s.
