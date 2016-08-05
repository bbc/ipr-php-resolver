<?php

namespace BBC\iPlayerRadio\Resolver\Tests;

use BBC\iPlayerRadio\Resolver\PHPUnit\TestCase;
use BBC\iPlayerRadio\Resolver\UnresolvableRequirementException;

class UnresolvableRequirementExceptionTest extends TestCase
{
    public function testSetGetFailedRequirement()
    {
        $excp = new UnresolvableRequirementException();
        $this->assertNull($excp->getFailedRequirement());
        $this->assertEquals($excp, $excp->setFailedRequirement('unknown'));
        $this->assertEquals('unknown', $excp->getFailedRequirement());
    }
}
