<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

/**
 * Class RandomNumberBackend
 *
 * This resolver backend simply returns a random number for each of the
 * requirements.
 *
 * @package     BBC\iPlayerRadio\Resolver\Tests\Mocks
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class RandomNumberBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'random_number' || $requirement == 'random_number_subrequirement';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            if ($req === 'random_number') {
                $results[] = rand(0, 100);
            } else {
                $results[] = new ObjectRequiresNumber();
            }
        }
        return $results;
    }
}
