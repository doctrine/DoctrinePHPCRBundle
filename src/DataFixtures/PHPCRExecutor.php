<?php

namespace Doctrine\Bundle\PHPCRBundle\DataFixtures;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor as BasePHPCRExecutor;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PHPCRExecutor extends BasePHPCRExecutor
{
    private $initializerManager;

    public function __construct(
        DocumentManagerInterface $dm,
        PHPCRPurger $purger = null,
        InitializerManager $initializerManager = null
    ) {
        parent::__construct($dm, $purger);

        $this->initializerManager = $initializerManager;
    }

    public function purge()
    {
        parent::purge();

        if ($this->initializerManager) {
            $this->initializerManager->setLoggingClosure($this->logger);
            $this->initializerManager->initialize();
        }
    }
}
