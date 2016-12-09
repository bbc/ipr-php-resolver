<?php

namespace BBC\iPlayerRadio\Resolver\Tests;

use BBC\iPlayerRadio\Resolver\PHPUnit\TestCase;
use BBC\iPlayerRadio\Resolver\Resolver;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\CircularBackend;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\MultiDArrayRequireBackend;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\MultiYieldBackend;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectRequiresCircular;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectRequiresNumber;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectRequiresString;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectRequiresSubrequirement;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectWithMultipleYields;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\ObjectYieldingMultiYield;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\RandomNumberBackend;
use BBC\iPlayerRadio\Resolver\Tests\Mocks\RandomStringBackend;

class ResolverTest extends TestCase
{
    public function testAddingBackends()
    {
        $resolver = new Resolver();
        $stringBackend = new RandomStringBackend();

        $this->assertEquals($resolver, $resolver->addBackend($stringBackend));
        $this->assertEquals([$stringBackend], $resolver->getBackends());
    }

    /**
     * @expectedException           \BBC\iPlayerRadio\Resolver\UnresolvableRequirementException
     * @expectedExceptionMessage    No backend supports requirement
     */
    public function testUnresolvableRequirement()
    {
        $resolver = new Resolver();
        $resolver->resolve(function () {
            yield 'Unknown';
        });
    }

    public function testSingleItemSinglePhaseResolution()
    {
        $stringBackend = new RandomStringBackend();

        $resolver = new Resolver();
        $resolver->addBackend($stringBackend);

        $stringObject = new ObjectRequiresString();

        $result = $resolver->resolve($stringObject);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result);
        $this->assertInternalType('string', $result->string);
    }

    public function testMultiItemSinglePhaseResolution()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());

        $stringObject = new ObjectRequiresString();
        $stringObject2 = new ObjectRequiresString();

        $result = $resolver->resolve([$stringObject, $stringObject2]);

        $this->assertTrue(is_array($result));
        $this->assertCount(2, $result);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result[0]);
        $this->assertInternalType('string', $result[0]->string);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result[1]);
        $this->assertInternalType('string', $result[1]->string);
    }

    public function testMixedBackends()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());

        $stringObject = new ObjectRequiresString();
        $numberObject = new ObjectRequiresNumber();

        $result = $resolver->resolve([$stringObject, $numberObject]);

        $this->assertTrue(is_array($result));
        $this->assertCount(2, $result);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result[0]);
        $this->assertInternalType('string', $result[0]->string);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresNumber', $result[1]);
        $this->assertInternalType('integer', $result[1]->number);
    }

    public function testMixedBackendOrder()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());

        $stringObject   = new ObjectRequiresString();
        $numberObject   = new ObjectRequiresNumber();
        $stringObject2  = new ObjectRequiresString();

        $result = $resolver->resolve([$stringObject, $numberObject, $stringObject2]);

        $this->assertTrue(is_array($result));
        $this->assertCount(3, $result);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result[0]);
        $this->assertInternalType('string', $result[0]->string);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresNumber', $result[1]);
        $this->assertInternalType('integer', $result[1]->number);

        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresString', $result[2]);
        $this->assertInternalType('string', $result[2]->string);
    }

    public function testSubrequirements()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());

        $complexObject = new ObjectRequiresSubrequirement();

        $result = $resolver->resolve($complexObject);

        $this->assertTrue(isset($result->number));
        $this->assertInstanceOf('BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectRequiresNumber', $result->number);
        $this->assertInternalType('integer', $result->number->number);

        $runs = $resolver->getRuns();
        $this->assertCount(1, $runs);
        $this->assertCount(2, $runs[0]); // number of phases.
    }

    public function testMultipleYieldSingleObject()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());

        $multipleYieldObject = new ObjectWithMultipleYields();

        $result = $resolver->resolve($multipleYieldObject);

        $this->assertInstanceOf(
            'BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectWithMultipleYields',
            $result
        );
        $this->assertInternalType('string', $result->string);
        $this->assertInternalType('integer', $result->number);

        $runs = $resolver->getRuns();
        $this->assertCount(1, $runs);
        $this->assertCount(2, $runs[0]); // number of phases.
    }

    public function testFirstPhaseRequiresMultiYield()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomStringBackend());
        $resolver->addBackend(new RandomNumberBackend());
        $resolver->addBackend(new MultiYieldBackend());

        $objectYieldingMultiYield = new ObjectYieldingMultiYield();

        $result = $resolver->resolve($objectYieldingMultiYield);

        $this->assertInstanceOf(
            'BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectYieldingMultiYield',
            $result
        );
        $this->assertInstanceOf(
            'BBC\\iPlayerRadio\\Resolver\\Tests\\Mocks\\ObjectWithMultipleYields',
            $result->sub
        );
        $this->assertInternalType('string', $result->sub->string);
        $this->assertInternalType('integer', $result->sub->number);

        $runs = $resolver->getRuns();
        $this->assertCount(1, $runs);
        $this->assertCount(3, $runs[0]); // number of phases.
    }

    public function testAnonymousRequirements()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new RandomNumberBackend());

        $resolver->resolve(function () use (&$result) {
            $result = (yield 'random_number');
        });

        $this->assertInternalType('integer', $result);

        $runs = $resolver->getRuns();
        $this->assertCount(1, $runs);
        $this->assertCount(1, $runs[0]); // number of phases.
    }

    public function testMaxPhases()
    {
        $resolver = new Resolver();
        $resolver->addBackend(new CircularBackend());
        $this->assertEquals(5, $resolver->getMaxPhases());
        $this->assertEquals($resolver, $resolver->setMaxPhases(6));

        $object = new ObjectRequiresCircular();
        $resolver->resolve($object);

        // Ensure that we only went through 6 phases;
        $runs = $resolver->getRuns();
        $this->assertCount(1, $runs);
        $this->assertCount(6, $runs[0]); // phases
    }

    public function testResultMultiArrayRequirements()
    {
        $resolver = new Resolver();
        $resolver
            ->addBackend(new MultiDArrayRequireBackend())
            ->addBackend(new RandomNumberBackend())
            ->addBackend(new RandomStringBackend())
        ;

        $resolver->resolve(function () use (&$result) {
            $result = (yield 'multi_d_yield');
        });

        $this->assertCount(1, $result);
        $this->assertCount(3, $result[0]);

        $this->assertInternalType('integer', $result[0][0]->number);
        $this->assertInternalType('string', $result[0][1]->string);
        $this->assertInternalType('integer', $result[0][2]->number);
    }
}
