<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

/**
 * Class RandomStringBackend
 *
 * This resolver backend simply returns a random string for each of the
 * requirements.
 *
 * @package     BBC\iPlayerRadio\Resolver\Tests\Mocks
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class RandomStringBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'random_string';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            $pool = 'abcdefghijklmnopqrstuvwxyz';
            $length = rand(4, 8);
            $result = '';
            for ($i = 0; $i < $length; $i++) {
                $result .= $pool[rand(0, 25)];
            }
            $results[] = $result;
        }
        return $results;
    }
}
