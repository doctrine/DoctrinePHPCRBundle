<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;

/**
 * Functional test for generic initializer.
 */
class GenericInitializerTest extends BaseTestCase
{
    /**
     * Check the initializer idempotency.
     */
    public function testIdempotency()
    {
        $initializer = new GenericInitializer('test', ['/test/path']);
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = self::createClient()->getContainer()->get('doctrine_phpcr');
        $dm = $managerRegistry->getManager();

        // The first run should create a node.
        $this->assertNull($dm->find(null, '/test/path'));

        $initializer->init($managerRegistry);
        $node = $dm->find(null, '/test/path');
        $this->assertNotNull($node);

        // The second run should not modify the existing node.
        $initializer->init($managerRegistry);
        $this->assertSame($node, $dm->find(null, '/test/path'));

        $managerRegistry->getConnection()->save();
    }
}
