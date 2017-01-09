<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

class IteratorBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'iterator';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            $results[] = new SimpleIterator([
                new ObjectRequiresString(),
                new ObjectRequiresNumber()
            ]);
        }
        return $results;
    }
}
