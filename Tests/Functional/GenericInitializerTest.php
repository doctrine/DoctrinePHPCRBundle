<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

/**
 * Functional test for generic initializer
 */
class GenericInitializerTest extends BaseTestCase
{
    /**
     * Test what happens when two initializers try to create the same base path.
     */
    public function testInitializerTwice()
    {
        $initializer = new GenericInitializer('test', array('/test/path'));
        /** @var ManagerRegistry $registry */
        $registry = $this->getContainer()->get('doctrine_phpcr');
        $initializer->init($registry);
        $initializer->init($registry);
        $registry->getConnection()->save();
    }
}
