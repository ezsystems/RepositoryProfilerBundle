===============
Profiler Bundle
===============

Bundle to profile eZ Platform API/SPI and setup scenarios to be able to
continuously test to keep track of performance regressions of repository and
underlying storage engines(s) 

Warning
=======

    Running the performance tests / profiling will chnage the contents of your
    database. Use with care.

Usage
=====

Install the bundle inside of an existing ez-platform installation::

    composer.phar require ezsystems/profiler-bundle dev-master

Then you can run the performance tests using::

    php ezpublish/console profiler:run papi vendor/ezsystems/profiler-bundle/docs/enterprise_example.php

The provided file specifies the performance test you want to run. The file
mentioned here is an example file provided with the bundle.


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
