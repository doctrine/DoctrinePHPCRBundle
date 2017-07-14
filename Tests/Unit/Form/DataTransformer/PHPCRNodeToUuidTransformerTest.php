<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;

class PHPCRNodeToUuidTransformerTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->session = $this->getMock('PHPCR\SessionInterface');
        $this->transformer = new PHPCRNodeToUuidTransformer($this->session);
        $this->node = $this->getMockBuilder('Jackalope\Node')
            ->disableOriginalConstructor()
            ->getMock();
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
