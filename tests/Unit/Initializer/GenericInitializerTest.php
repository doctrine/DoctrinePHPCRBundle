<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Jackalope\Node;
use Jackalope\NodeType\NodeTypeManager;
use PHPCR\SessionInterface;
use PHPCR\WorkspaceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericInitializerTest extends TestCase
{
    protected ManagerRegistryInterface $registry;
    /**
     * @var SessionInterface&MockObject
     */
    private SessionInterface $session;
    /**
     * @var WorkspaceInterface&MockObject
     */
    private WorkspaceInterface $workspace;
    /**
     * @var NodeTypeManager&MockObject
     */
    private NodeTypeManager $nodeTypeManager;
    /**
     * @var Node&MockObject
     */
    private Node $node;

    public function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistryInterface::class);

        $this->session = $this->createMock(SessionInterface::class);
        $this->workspace = $this->createMock(WorkspaceInterface::class);
        $this->nodeTypeManager = $this->createMock(NodeTypeManager::class);
        $this->node = $this->createMock(Node::class);
    }

    public function provideInitializer(): array
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
    public function testInitializer(string $name, array $basePaths, string $cnd): void
    {
        $this->registry->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->session);

        if ($cnd) {
            $this->session->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($this->workspace);
            $this->workspace->expects($this->once())
                ->method('getNodeTypeManager')
                ->willReturn($this->nodeTypeManager);
            $this->nodeTypeManager->expects($this->once())
                ->method('registerNodeTypesCnd')
                ->with($cnd);
        }

        if ($basePaths) {
            $this->node->expects($this->any())
                ->method('addNode')
                ->willReturn($this->node);
            $this->session->expects($this->exactly(\count($basePaths)))
                ->method('getRootNode')
                ->willReturn($this->node);
        }

        $initializer = new GenericInitializer($name, $basePaths, $cnd);
        $initializer->init($this->registry);
        $this->assertEquals($name, $initializer->getName());
    }
}
