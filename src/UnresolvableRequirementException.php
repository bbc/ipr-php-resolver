<?php

namespace BBC\iPlayerRadio\Resolver;

/**
 * Class UnresolvableRequirementException
 *
 * Thrown when the resolver can't find a backend for a given requirement
 *
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class UnresolvableRequirementException extends \Exception
{
    /**
     * @var     mixed
     */
    protected $requirement;

    /**
     * Sets the requirement (or more specifically the resolution) which could not be
     * attributed to any of the backends.
     *
     * @param   mixed   $requirement
     * @return  $this
     */
    public function setFailedRequirement($requirement)
    {
        $this->requirement = $requirement;
        return $this;
    }

    /**
     * Returns the requirement resolution which could not be resolved
     *
     * @return  mixed
     */
    public function getFailedRequirement()
    {
        return $this->requirement;
    }
}
