<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use Jackalope\Node;
use PHPCR\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PHPCRNodeToUuidTransformerTest extends Testcase
{
    /**
     * @var SessionInterface&MockObject
     */
    private SessionInterface $session;

    /**
     * @var PHPCRNodeToUuidTransformer&MockObject
     */
    private PHPCRNodeToUuidTransformer $transformer;

    /**
     * @var Node&MockObject
     */
    private Node $node;

    public function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->transformer = new PHPCRNodeToUuidTransformer($this->session);
        $this->node = $this->createMock(Node::class);
    }

    public function testTransform(): void
    {
        $this->node->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('/asd');
        $res = $this->transformer->transform($this->node);
        $this->assertEquals('/asd', $res);
    }

    public function testReverseTransform(): void
    {
        $this->session->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with('/asd')
            ->willReturn($this->node);

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->node, $res);
    }

    public function testReverseTransformEmpty(): void
    {
        $this->session->expects($this->never())
            ->method('getNodeByIdentifier');
        $res = $this->transformer->reverseTransform('');
        $this->assertNull($res);
    }
}
