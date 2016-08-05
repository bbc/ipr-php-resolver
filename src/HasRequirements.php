<?php

namespace BBC\iPlayerRadio\Resolver;

/**
 * Interface HasRequirements
 *
 * Interface for objects that will require extra work to hydrate them into a usable state.
 *
 * @package    BBC\iPlayerRadio\Resolver
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
interface HasRequirements
{
    /**
     * This function should 'yield' requirements. For example:
     *
     *  public function requires()
     *  {
     *      $this->latestProgrammes = (yield new LatestProgrammesRequirement());
     *  }
     *
     * DO NOT RETURN from this function, you'll get a fatal error.
     *
     * Each yield is placed into a separate "phase", so you can use the result of the previous
     * operation in the next yield:
     *
     *  public function requires()
     *  {
     *      $this->latestProgrammes = (yield new LatestProgrammesRequirement());
     *      $this->latestProgrammesHydrated = (yield new HydratedRequirement($this->latestProgrammes));
     *  }
     *
     * @param   array $flags Array of "flags" which can be used to determine what is required.
     * @return
     */
    public function requires(array $flags = []);
}
