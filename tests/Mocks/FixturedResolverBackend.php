<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

use BBC\iPlayerRadio\Resolver\ResolverBackend;

/**
 * Class FixturedResolverBackend
 *
 * I considered naming this PromiscuousResolverBackend or SluiceResolverBackend but neither
 * seemed appropriate. This resolver backend is useful for unit tests where you just want to
 * return a given set of resolutions in order. So you can queue up Nitro/Blur responses for instance.
 *
 * The responses are pulled off one-per-requirement, and if there's multiple passes, we keep walking the
 * array, so you can use this with "nested" requirements.
 *
 * @package     BBC\iPlayerRadio\Resolver\Tests\Mocks
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class FixturedResolverBackend implements ResolverBackend
{
    protected $responses = [];
    protected $pointer = 0;

    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    /**
     * Returns whether this backend can handle a given Requirement.
     *
     * @param   mixed   $requirement
     * @return  bool
     */
    public function canResolve($requirement)
    {
        return true; // yep, anything.
    }

    /**
     * Given a list of requirements, perform their resolutions.
     *
     * @param   array   $requirements
     * @return  array
     */
    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            if (!array_key_exists($this->pointer, $this->responses)) {
                throw new \LogicException('Ran out of pre-baked responses at loop '.$this->pointer);
            }
            $results[] = $this->responses[$this->pointer];

            $this->pointer ++;
        }
        return $results;
    }
}
