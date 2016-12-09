<?php

namespace BBC\iPlayerRadio\Resolver;

/**
 * Class Resolver
 *
 * The resolvers job is to take an array of items with Requirements, and "resolve" all of them in the most
 * efficient way possible.
 *
 * @package     BBC\iPlayerRadio\Resolver
 * @author      Alex Gisby <alex.gisby@bbc.co.uk>
 * @copyright   BBC
 */
class Resolver
{
    /**
     * @var     ResolverBackend[]
     */
    protected $backends = [];

    /**
     * @var     array
     */
    protected $runs = [];

    /**
     * @var     int
     */
    protected $maxPhases = 5;

    /**
     * Adds a new backend onto the resolver
     *
     * @param   ResolverBackend     $backend
     * @return  $this
     */
    public function addBackend(ResolverBackend $backend)
    {
        $this->backends[] = $backend;
        return $this;
    }

    /**
     * Returns all the backends that we have added
     *
     * @return  ResolverBackend[]
     */
    public function getBackends()
    {
        return $this->backends;
    }

    /**
     * Returns all of the runs the resolver made.
     *
     * @return  array
     */
    public function getRuns()
    {
        return $this->runs;
    }

    /**
     * Sets the maximum number of phases each run is allowed to go through before cutting out.
     * This stops circular dependencies.
     *
     * @param   int     $maxPhases
     * @return  $this
     */
    public function setMaxPhases($maxPhases)
    {
        $this->maxPhases = $maxPhases;
        return $this;
    }

    /**
     * Returns the maximum number of phases each run is allowed to go through before cutting out.
     * This stops circular dependencies.
     *
     * @return  int
     */
    public function getMaxPhases()
    {
        return $this->maxPhases;
    }

    /**
     * Resolves all the dependencies in the queue, calling itself recursively if
     * there are still things to resolve at the end of the loop.
     *
     * @param   mixed
     * @param   array   $flags  An array of flags to help requires() blocks work out the right thing to do.
     * @return  mixed
     * @throws  UnresolvableRequirementException
     */
    public function resolve($requirements, array $flags = [])
    {
        $runPhases = [];

        $list = (is_array($requirements))? $requirements : [$requirements];

        /* @var     \Generator  $pool   */
        $pool = [];
        foreach ($list as $req) {
            if ($req instanceof \Closure) {
                $pool[] = $req();
            } elseif ($req instanceof HasRequirements) {
                $pool[] = $req->requires($flags);
            }
        }

        $phaseCount = 0;
        while ($pool && $phaseCount < $this->maxPhases) {
            $phase = [
                'start' => microtime(true),
                'resolutions' => []
            ];
            $backendMap = [];
            foreach ($pool as $target) {
                $resolution = $target->current();

                if ($resolution) {
                    $canResolve = false;
                    foreach ($this->backends as $backendIndex => $backend) {
                        if ($backend->canResolve($resolution)) {
                            $backendMap[$backendIndex]['resolutions'][] = $resolution;
                            $backendMap[$backendIndex]['targets'][] = $target;
                            $canResolve = true;
                        }
                    }

                    if (!$canResolve) {
                        $excp = new UnresolvableRequirementException('No backend supports requirement');
                        $excp->setFailedRequirement($resolution);
                        throw $excp;
                    }
                }
                $phase['resolutions'][] = $resolution;
            }

            $pool = [];

            // Loop each backend and resolve:
            foreach ($backendMap as $backendIndex => $backendTasks) {
                $results = $this->backends[$backendIndex]->doResolve($backendTasks['resolutions']);

                foreach ($results as $i => $result) {
                    // Send the result to the target generator:
                    /* @var     \Generator  $targetGenerator    */
                    $targetGenerator = $backendTasks['targets'][$i];
                    $newRequirement = $targetGenerator->send($result);

                    // If this object isn't done sending requirements, head back into
                    // the loop:
                    if ($newRequirement) {
                        $pool[] = $backendTasks['targets'][$i];
                    }

                    // Add results into the pool of potentials:
                    $resultsArray = (is_array($result) || $result instanceof \ArrayAccess)? $result : [$result];

                    // Use a recursive iterator for nested arrays:
                    $iter = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($resultsArray, \RecursiveArrayIterator::CHILD_ARRAYS_ONLY));
                    foreach ($iter as $singleResult) {
                        if (is_object($singleResult) && $singleResult instanceof HasRequirements) {
                            $pool[] = $singleResult->requires($flags);
                        }
                    }
                }
            }
            $phase['end'] = microtime(true);
            $runPhases[] = $phase;
            $phaseCount ++;
        }

        $this->runs[] = $runPhases;

        return $requirements;
    }
}
