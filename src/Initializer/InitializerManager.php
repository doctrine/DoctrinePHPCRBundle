<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

/**
 * Service which is used to aggregate and execute the initializers.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class InitializerManager
{
    /**
     * @var InitializerInterface[]
     */
    protected $initializers = array();

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var \Closure
     */
    protected $loggingClosure = null;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Set a logging closure for use, for example, from a
     * console command.
     *
     * @param \Closure $closure
     */
    public function setLoggingClosure(\Closure $closure = null)
    {
        $this->loggingClosure = $closure;
    }

    /**
     * Add an initializer.
     *
     * @param InitializerInterface $initializer
     */
    public function addInitializer(InitializerInterface $initializer, $priority = 0)
    {
        if (empty($this->initializers[$priority])) {
            $this->initializers[$priority] = array();
        }

        $this->initializers[$priority][] = $initializer;
        $this->sortedInitializers = array();
    }

    /**
     * Iterate over the registered initializers and execute each of them.
     */
    public function initialize($sessionName = null)
    {
        $loggingClosure = $this->loggingClosure;

        foreach ($this->getInitializers() as $initializer) {
            if ($loggingClosure) {
                $loggingClosure(sprintf('<info>Executing initializer:</info> %s', $initializer->getName()));
            }

            // handle specified session if present
            if ($sessionName) {
                if (in_array('Doctrine\Bundle\PHPCRBundle\Initializer\SessionAwareInitializerInterface', class_implements($initializer))) {
                    $initializer->setSessionName($sessionName);
                } elseif ($loggingClosure) {
                    $loggingClosure(sprintf('<comment>Initializer "%s" does not implement SessionAwareInitializerInterface, "session" parameter will be ommitted.</comment>', $initializer->getName()));
                }
            }

            $initializer->init($this->registry);
        }
    }

    /**
     * Return the ordered initializers.
     *
     * @return InitializerInterface[]
     */
    private function getInitializers()
    {
        if (empty($this->sortedInitializers)) {
            $this->sortedInitializers = $this->sortInitializers();
        }

        return $this->sortedInitializers;
    }

    /**
     * Sort initializers by priority.
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return InitializerInterface[]
     */
    private function sortInitializers()
    {
        $sortedInitializers = array();
        krsort($this->initializers);

        foreach ($this->initializers as $initializers) {
            $sortedInitializers = array_merge($sortedInitializers, $initializers);
        }

        return $sortedInitializers;
    }
}
