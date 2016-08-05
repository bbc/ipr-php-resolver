<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\HasRequirements;

class ObjectRequiresString implements HasRequirements
{
    public $string;

    public function requires(array $flags = [])
    {
        $this->string = (yield 'random_string');
    }
}
