<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

/**
 * Class MultiYieldBackend
 *
 * A backend that returns objects with multiple-yield requires() functions.
 *
 * @package     BBC\iPlayerRadio\Resolver\Tests\MocksNeo
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class MultiYieldBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'multi_yield';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            $results[] = new ObjectWithMultipleYields();
        }
        return $results;
    }
}
