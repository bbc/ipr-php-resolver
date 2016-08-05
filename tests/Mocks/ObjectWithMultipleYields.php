<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\HasRequirements;

class ObjectWithMultipleYields implements HasRequirements
{
    public $number;
    public $string;

    public function requires(array $flags = [])
    {
        $this->number = (yield 'random_number');
        $this->string = (yield 'random_string');
    }
}
