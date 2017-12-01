<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use Jackalope\Node;
use PHPCR\SessionInterface;
use PHPUnit\Framework\TestCase;

class PHPCRNodeToUuidTransformerTest extends Testcase
{
    /**
     * @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var PHPCRNodeToUuidTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformer;

    /**
     * @var Node|\PHPUnit_Framework_MockObject_MockObject
     */
    private $node;

    public function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->transformer = new PHPCRNodeToUuidTransformer($this->session);
        $this->node = $this->createMock(Node::class);
    }

    public function testTransform()
    {
        $this->node->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('/asd'));
        $res = $this->transformer->transform($this->node);
        $this->assertEquals('/asd', $res);
    }

    public function testReverseTransform()
    {
        $this->session->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with('/asd')
            ->will($this->returnValue($this->node));

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->node, $res);
    }

    public function testReverseTransformEmpty()
    {
        $this->session->expects($this->never())
            ->method('getNodeByIdentifier');
        $res = $this->transformer->reverseTransform('');
        $this->assertNull($res);
    }
}
