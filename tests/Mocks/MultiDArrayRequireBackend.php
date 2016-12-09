<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

class MultiDArrayRequireBackend implements ResolverBackend
{
    public function canResolve($requirement)
    {
        return $requirement == 'multi_d_yield';
    }

    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            $results[] = [[
                new ObjectRequiresNumber(),
                new ObjectRequiresString(),
                new ObjectRequiresNumber(),
            ]];
        }
        return $results;
    }
}
