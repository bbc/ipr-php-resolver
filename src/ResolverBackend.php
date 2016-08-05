<?php

namespace BBC\iPlayerRadio\Resolver;

/**
 * Interface ResolverBackend
 *
 * Common interface for all resolver backends to implement.
 *
 * @package     BBC\iPlayerRadio\Resolver
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
interface ResolverBackend
{
    /**
     * Returns whether this backend can handle a given Requirement. Requirements
     * can be absolutely anything, so make sure to verify correctly against it.
     *
     * @param   mixed   $requirement
     * @return  bool
     */
    public function canResolve($requirement);

    /**
     * Given a list of requirements, perform their resolutions. Requirements can
     * be absolutely anything from strings to full-bore objects.
     *
     * @param   array   $requirements
     * @return  array
     */
    public function doResolve(array $requirements);
}
