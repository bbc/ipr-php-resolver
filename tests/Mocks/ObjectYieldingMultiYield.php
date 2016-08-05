<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\HasRequirements;

class ObjectYieldingMultiYield implements HasRequirements
{
    public $sub;

    public function requires(array $flags = [])
    {
        $this->sub = (yield 'multi_yield');
    }
}
