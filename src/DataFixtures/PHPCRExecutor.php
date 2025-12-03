<?php

namespace Doctrine\Bundle\PHPCRBundle\DataFixtures;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor as BasePHPCRExecutor;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
final class PHPCRExecutor extends AbstractExecutor
{
    private BasePHPCRExecutor $wrappedExecutor;

    public function __construct(
        DocumentManagerInterface $dm,
        ?PHPCRPurger $purger = null,
        private ?InitializerManager $initializerManager = null,
    ) {
        parent::__construct($dm);
        $this->wrappedExecutor = new BasePHPCRExecutor($dm, $purger);
    }

    public function purge(): void
    {
        parent::purge();

        if ($this->initializerManager) {
            $this->initializerManager->setLoggingClosure($this->logger);
            $this->initializerManager->initialize();
        }
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        $this->wrappedExecutor->execute($fixtures, $append);
    }

    public function getObjectManager(): DocumentManagerInterface
    {
        return $this->wrappedExecutor->getObjectManager();
    }
}
