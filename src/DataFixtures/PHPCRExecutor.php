<?php


namespace Doctrine\Bundle\PHPCRBundle\DataFixtures;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor as BasePHPCRExecutor;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PHPCRExecutor extends BasePHPCRExecutor
{
    protected $initializerManager;

    /**
     * Construct new fixtures loader instance.
     *
     * @param DocumentManager $dm manager instance used for persisting the fixtures
     */
    public function __construct(
        DocumentManager $dm,
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
