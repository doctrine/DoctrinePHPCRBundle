<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;

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
    private array $initializers = [];

    private ManagerRegistryInterface $registry;
    private ?\Closure $loggingClosure = null;

    /**
     * @var InitializerInterface[]
     */
    private array $sortedInitializers;

    public function __construct(ManagerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function setLoggingClosure(\Closure $closure = null): void
    {
        $this->loggingClosure = $closure;
    }

    /**
     * Add an initializer to this manager at the specified priority.
     *
     * @param int $priority The higher the number, the earlier the initializer is executed
     */
    public function addInitializer(InitializerInterface $initializer, int $priority = 0): void
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
    public function initialize(string $sessionName = null): void
    {
        $loggingClosure = $this->loggingClosure;

        foreach ($this->getInitializers() as $initializer) {
            if ($loggingClosure) {
                $loggingClosure(sprintf('<info>Executing initializer:</info> %s', $initializer->getName()));
            }

            // handle specified session if present
            if ($sessionName) {
                if ($initializer instanceof SessionAwareInitializerInterface) {
                    $initializer->setSessionName($sessionName);
                } elseif ($loggingClosure) {
                    $loggingClosure(sprintf('<comment>Initializer "%s" does not implement SessionAwareInitializerInterface, "session" parameter will be omitted.</comment>', $initializer->getName()));
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
