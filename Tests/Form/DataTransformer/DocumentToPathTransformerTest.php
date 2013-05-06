<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;

class DocumentToPathTransformerTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uow = $this->getMockBuilder('Doctrine\ODM\PHPCR\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = new DocumentToPathTransformer($this->dm);
        $this->document = new \stdClass;
    }

    public function testTransform()
    {
        $this->dm->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));
        $this->uow->expects($this->once())
            ->method('getDocumentId')
            ->with($this->document)
            ->will($this->returnValue('/asd'));

        $res = $this->transformer->transform($this->document);
        $this->assertEquals('/asd', $res);
    }

    public function testReverseTransform()
    {
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/asd')
            ->will($this->returnValue($this->document));

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->document, $res);
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformNotFound()
    {
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/asd')
            ->will($this->returnValue(null));

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->document, $res);
    }

    public function testReverseTransformEmpty()
    {
        $this->dm->expects($this->never())
            ->method('find');
        $res = $this->transformer->reverseTransform('');
        $this->assertNull($res);
    }
}
