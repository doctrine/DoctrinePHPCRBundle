<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Jackalope\Node;
use PHPCR\SessionInterface;
use PHPUnit\Framework\TestCase;

class PHPCRNodeToPathTransformerTest extends Testcase
{
    /**
     * @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var PHPCRNodeToPathTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformer;

    /**
     * @var Node|\PHPUnit_Framework_MockObject_MockObject
     */
    private $node;

    public function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->transformer = new PHPCRNodeToPathTransformer($this->session);
        $this->node = $this->createMock(Node::class);
    }

    public function testTransform()
    {
        $this->node->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/asd'));
        $res = $this->transformer->transform($this->node);
        $this->assertEquals('/asd', $res);
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testTransformWrongType()
    {
        $this->transformer->transform(new \stdClass());
    }

    public function testReverseTransform()
    {
        $this->session->expects($this->once())
            ->method('getNode')
            ->with('/asd')
            ->will($this->returnValue($this->node));

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->node, $res);
    }

    public function testReverseTransformEmpty()
    {
        $this->session->expects($this->never())
            ->method('getNode');
        $res = $this->transformer->reverseTransform('');
        $this->assertNull($res);
    }

    public function testReverseTransformNotFound()
    {
        // nothing to test, the PHPCR session will throw an
        // ItemNotFoundException
    }
}
