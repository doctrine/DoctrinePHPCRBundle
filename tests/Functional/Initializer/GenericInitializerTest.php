<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

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
        $registry = $this->getContainer()->get('doctrine_phpcr');
        $dm = $registry->getManager();

        // The first run should create a node.
        $this->assertNull($dm->find(null, '/test/path'));

        $initializer->init($registry);
        $node = $dm->find(null, '/test/path');
        $this->assertNotNull($node);

        // The second run should not modify the existing node.
        $initializer->init($registry);
        $this->assertSame($node, $dm->find(null, '/test/path'));

        $registry->getConnection()->save();
    }
}
