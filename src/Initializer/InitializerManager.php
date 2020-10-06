<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

/**
 * Aggregates and executes initializers.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class InitializerManager
{
    /**
     * @var InitializerInterface[]
     */
    private $initializers = [];

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var \Closure
     */
    private $loggingClosure = null;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function setLoggingClosure(\Closure $closure = null)
    {
        $this->loggingClosure = $closure;
    }

    /**
     * Add an initializer to this manager at the specified priority.
     *
     * @param int $priority The higher the number, the earlier the initializer is executed
     */
    public function addInitializer(InitializerInterface $initializer, int $priority = 0)
    {
        if (empty($this->initializers[$priority])) {
            $this->initializers[$priority] = [];
        }

        $this->initializers[$priority][] = $initializer;
        $this->sortedInitializers = [];
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
                if (\in_array(SessionAwareInitializerInterface::class, class_implements($initializer))) {
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
    private function getInitializers(): array
    {
        if (empty($this->sortedInitializers)) {
            $this->sortedInitializers = $this->sortInitializers();
        }

        return $this->sortedInitializers;
    }

    /**
     * Sort initializers by priority.
     *
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return InitializerInterface[]
     */
    private function sortInitializers(): array
    {
        $sortedInitializers = [];
        krsort($this->initializers);

        foreach ($this->initializers as $initializers) {
            $sortedInitializers = array_merge($sortedInitializers, $initializers);
        }

        return $sortedInitializers;
    }
}
