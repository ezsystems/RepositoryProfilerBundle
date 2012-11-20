=================
Performance Tests
=================

This is a suite for performance tests for the API and SPI implementations of eZ
Publish.

Installation
============

To be able to run the performance tests, the following commands must be
executed::

    composer.phar install

    cd vendor/ezsystems/ezpublish/
    ln -s config.php-DEVELOPMENT config.php
    composer.phar install
    git clone https://github.com/ezsystems/ezpublish.git vendor/ezsystems/ezpublish-legacy

The last four steps are necessary to properly initialize ezp-next. Sadly this
is not done automatically (yet).

Running
=======

There is one example, which can provide you with a basic introduction on how to
run the performance tests, and should also be already working::

    php docs/enterprise_example.php


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
