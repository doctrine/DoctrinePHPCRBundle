<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;

class GenericInitializerTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\ManagerRegistry'
        )->disableOriginalConstructor()->getMock();

        $this->session = $this->getMock('PHPCR\SessionInterface');
        $this->workspace = $this->getMock('PHPCR\WorkspaceInterface');
        $this->nodeTypeManager = $this->getMockBuilder(
            'Jackalope\NodeType\NodeTypeManager'
        )->disableOriginalConstructor()->getMock();
        $this->node = $this->getMockBuilder(
            'Jackalope\Node'
        )->disableOriginalConstructor()->getMock();
    }

    public function provideInitializer()
    {
        return array(
            array(
                'test_init', array(
                    'foo/bar/1', 'foobar/2',
                ), 'this is CND',
            ),
        );
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
            $this->session->expects($this->exactly(count($basePaths)))
                ->method('getRootNode')
                ->will($this->returnValue($this->node));
        }

        $initializer = new GenericInitializer($name, $basePaths, $cnd);
        $initializer->init($this->registry);
        $this->assertEquals($name, $initializer->getName());
    }
}
