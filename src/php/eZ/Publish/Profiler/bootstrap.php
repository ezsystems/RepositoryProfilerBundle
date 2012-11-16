<?php
/**
 * This file is part of eZ Publish Profiler
 *
 * @version $Revision$
 */

namespace eZ\Publish\Profiler;

// @codeCoverageIgnoreStart
// @codingStandardsIgnoreStart

require __DIR__ . '/../../../../../vendor/autoload.php';

// @TODO: Remove hard system dep:
require '/home/kore/devel/ezp-next/bootstrap.php';

spl_autoload_register(
    function ( $class )
    {
        if ( 0 === strpos( $class, __NAMESPACE__ ) )
        {
            include __DIR__ . '/../../../' . strtr( $class, '\\', '/' ) . '.php';
        }
    }
);

// @codingStandardsIgnoreEnd
// @codeCoverageIgnoreEnd
