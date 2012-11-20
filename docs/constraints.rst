=======================
Performance Constraints
=======================

There are different constraints, when profiling the performance of eZ Publish
backends. First there are consraints regarding the data in the database, which
has effects on the performance of the full system. Second there are constraints
regarding the usage patterns of thew storage system, which may result in
different performance characteristics of the full system.

Data Dimensions
===============

First we have different aspects of the data available in the system, which are
partially dependent and may cause different performance issues. Important for
all setups are the following aspects of the data distribution:

- $n Content objects
  - $m ContentVersion
    - $o languages
- $n Field per Content / ContentType
  - $m "complex" FieldTypes
- $n relations
- $n ContentType
- $n Location (per Content)
  - Different tree structures
    - flat
    - deep
    - degenerated

PAPI Profiling Aspects
----------------------

When profiling the Public API, there are additional aspects in the data, which
must be considered and which may have implications on the performance on the
system:

- $n User
- $n UserGroup
- $n Limitation
  - $m complex limitations

Usage Aspects
=============

Depending on the system, the storage might be used in different ways. The most
important aspect usually is the the read vs. write distribution. The read vs.
write distribution might be different for the different entities in the system:

- Read vs. write:
  - Content
  - ContentType

Usually there are no writes on ContentType in a live system. Those are very
seldom and performed "offline".

Classic write operations on Content are:

- Editorial workflow (newspapers)
- User interactions: Comments / Ratings

Is the use case "Shop" still relevant? In this case additional usage patterns
must be incorporated.

There will be no sane way to setup database with a large dataset before running
the actual tests. Since the performance test suite is supposed to verify the
performance of different storage systems, we can only use the abastraction
layer to insert the test data. This will obviously have a similar performance
like the test itself (minus the read operations). One option could be to always
use the SPI for inserting the offset data, to at least omit permission
checks.

Profiling Aspects
-----------------

- Read
  - View Content / Location
  - Subtree listings (folder)
  - Search
    - What Queries?

.. note::
    Are there other common read use cases?
    * What about loadLocations() for content

- Write
  - Create new primary Content
  - Create new sub Content (comments)

.. note::
    Are there other common write use cases?
    * Swap Location
    * Hide / unhide Location

Examples
========

It must be possible to model different setups, with different data setups. An
example for an "Enterprise Setup" could look like:

Data Dimensions
---------------

- 1,000,000 Content objects
  - on average 3 versions (drafts)
    - on average 5 languages
- on average 10 fields per Content / ContentType
  - XmlText
  - Author (User)
  - on average 3 TextLine
  - Relation
  - Boolean
  - Keyword?
- on average 5 relations
- about 15 ContentType
- on average 3 Location per Content

This would result in the following table sizes:

``ezcontentobject_attribute``
    150,000,000 entries
``ezcontentobject_tree``
    3,000,000 entries

Usage Aspects
-------------

The Content is created over time and consists of comments and articles with a
ratio of 5:1. Comments are created only below articles. Articles are created in
a folder structure, which is on average 3 levels deep, and are associated with,
on average, 3 Locations. Articles contain, on average 3 images.

The frontend only displays articles, together with its comments. Every 100th
viewer posts a comment. Every 50th viewer issues a random search, one of:

- Fulltext
- Date-Range

Current State
=============

With a simple PHP based based abstraction to configure a test setup and an
interpreter based on the SPI we currently manage to insert about 60 to 120
field data values per second into MySQL. The SQLite-Memory storage is *a lot*
faster.

Current state on a Thinkpad X220::

    eZ\Publish\Profiler\Executor\SPI: 13.13s:
     * eZ\Publish\Profiler\Actor\Create: 410 (31.22 per second)
       - Minimum: 12.87 ms
       - Average: 31.36 ms
       - Maximum: 95.15 ms
    eZ\Publish\Profiler\Executor\PAPI: 186.66s:
     * eZ\Publish\Profiler\Actor\SubtreeView: 2308 (12.36 per second)
       - Minimum: 0.00 ms
       - Average: 66.79 ms
       - Maximum: 173.39 ms
     * eZ\Publish\Profiler\Actor\Create: 459 (2.46 per second)
       - Minimum: 34.37 ms
       - Average: 69.05 ms
       - Maximum: 140.79 ms


..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
