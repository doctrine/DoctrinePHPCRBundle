<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use Jackalope\Node;

class PHPCRNodeToUuidTransformerTest extends PHPCRNodeToPathTransformerTest
{
    public function setUp()
    {
        parent::setUp();
        $this->transformer = new PHPCRNodeToUuidTransformer($this->session);
    }

    public function testTransform()
    {
        $this->node->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('/asd'));
        $res = $this->transformer->transform($this->node);
        $this->assertEquals('/asd', $res);
    }
}
