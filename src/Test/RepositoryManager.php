<?php

namespace Doctrine\Bundle\PHPCRBundle\Test;

use Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;

/**
 * A helper class for tests to reset the repository and load fixtures.
 */
class RepositoryManager
{
    private PHPCRExecutor $executor;

    public function __construct(
        private ManagerRegistryInterface $managerRegistry,
        private InitializerManager $initializerManager,
    ) {
    }

    public function getRegistry(): ManagerRegistryInterface
    {
        return $this->managerRegistry;
    }

    public function getDocumentManager(string $managerName = null): DocumentManagerInterface
    {
        return $this->getRegistry()->getManager($managerName);
    }

    /**
     * @param bool $initialize whether the PHPCR repository should also be initialized
     */
    public function purgeRepository(bool $initialize = false): void
    {
        $this->getExecutor($initialize)->purge();
    }

    /**
     * Load fixtures, taking into account possible DependentFixtureInterface fixtures.
     *
     * @param string[] $classNames FQN for the fixture classes to load
     * @param bool     $initialize whether the PHPCR repository should also be initialized
     *
     * @throws \InvalidArgumentException if any of the $classNames do not exist
     */
    public function loadFixtures(array $classNames, bool $initialize = false): void
    {
        $loader = new Loader();

        foreach ($classNames as $className) {
            $this->loadFixture($loader, $className);
        }

        $this->getExecutor($initialize)->execute($loader->getFixtures(), false);
    }

    /**
     * Recursively load the specified fixtures and their dependent fixtures.
     *
     * @throws \InvalidArgumentException if $className does not exist
     */
    private function loadFixture(Loader $loader, string $className): void
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Fixture class "%s" does not exist.',
                $className
            ));
        }

        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);

            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                $this->loadFixture($loader, $dependency);
            }
        }
    }

    private function getExecutor(bool $initialize = false): PHPCRExecutor
    {
        static $lastInitialize = null;

        if (isset($this->executor) && $initialize === $lastInitialize) {
            return $this->executor;
        }

        $initializerManager = $initialize ? $this->initializerManager : null;
        $purger = new PHPCRPurger();
        $executor = new PHPCRExecutor($this->getDocumentManager(), $purger, $initializerManager);
        $referenceRepository = new ProxyReferenceRepository($this->getDocumentManager());
        $executor->setReferenceRepository($referenceRepository);

        $this->executor = $executor;
        $lastInitialize = $initialize;

        return $executor;
    }
}
