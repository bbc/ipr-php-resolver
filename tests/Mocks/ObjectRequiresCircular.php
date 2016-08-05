<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\HasRequirements;

class ObjectRequiresCircular implements HasRequirements
{
    public $number;

    public function requires(array $flags = [])
    {
        $this->number = (yield 'circular');
    }
}
