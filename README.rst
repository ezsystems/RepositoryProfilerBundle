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
mentioned here is an example file provided with the bundle.

Configuration
=============

@TODO: Write

------------
jMeter Tests
------------

Usage
=====

The jMeter test can be run by just executing ``ant`` in the root directory. In
the first run jMeter will be downloaded. In subsequent runs the already
downloaded files will be used.

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
