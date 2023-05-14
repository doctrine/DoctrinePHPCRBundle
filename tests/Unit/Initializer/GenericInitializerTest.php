<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Jackalope\Node;
use Jackalope\NodeType\NodeTypeManager;
use PHPCR\SessionInterface;
use PHPCR\WorkspaceInterface;
use PHPUnit\Framework\TestCase;

class GenericInitializerTest extends TestCase
{
    protected $registry;

    public function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistryInterface::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->workspace = $this->createMock(WorkspaceInterface::class);
        $this->nodeTypeManager = $this->createMock(NodeTypeManager::class);
        $this->node = $this->createMock(Node::class);
    }

    public function provideInitializer()
    {
        return [
            [
                'test_init', [
                    'foo/bar/1', 'foobar/2',
                ], 'this is CND',
            ],
        ];
    }

    /**
     * @dataProvider provideInitializer
     */
    public function testInitializer($name, $basePaths, $cnd)
    {
        $this->registry->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->session));

        if ($cnd) {
            $this->session->expects($this->once())
                ->method('getWorkspace')
                ->will($this->returnValue($this->workspace));
            $this->workspace->expects($this->once())
                ->method('getNodeTypeManager')
                ->will($this->returnValue($this->nodeTypeManager));
            $this->nodeTypeManager->expects($this->once())
                ->method('registerNodeTypesCnd')
                ->with($cnd);
        }

        if ($basePaths) {
            $this->node->expects($this->any())
                ->method('addNode')
                ->will($this->returnValue($this->node));
            $this->session->expects($this->exactly(\count($basePaths)))
                ->method('getRootNode')
                ->will($this->returnValue($this->node));
        }

        $initializer = new GenericInitializer($name, $basePaths, $cnd);
        $initializer->init($this->registry);
        $this->assertEquals($name, $initializer->getName());
    }
}
