<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

/**
 * Class CircularBackend
 *
 * This resolver backend returns an object which requires itself.
 *
 * @package     BBC\iPlayerRadio\Resolver\Tests\Mocks
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class CircularBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'circular';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            $results[] = new ObjectRequiresCircular();
        }
        return $results;
    }
}
