<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DocumentToPathTransformerTest extends Testcase
{
    /**
     * @var DocumentManager&MockObject
     */
    private DocumentManagerInterface $dm;

    /**
     * @var UnitOfWork&MockObject
     */
    private UnitOfWork $uow;

    private DocumentToPathTransformer $transformer;
    private \stdClass $document;

    public function setUp(): void
    {
        $this->dm = $this->createMock(DocumentManagerInterface::class);
        $this->uow = $this->createMock(UnitOfWork::class);
        $this->transformer = new DocumentToPathTransformer($this->dm);
        $this->document = new \stdClass();
    }

    public function testTransform(): void
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

    public function testReverseTransform(): void
    {
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/asd')
            ->will($this->returnValue($this->document));

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->document, $res);
    }

    public function testReverseTransformNotFound(): void
    {
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/asd')
            ->will($this->returnValue(null));

        $this->expectException(TransformationFailedException::class);

        $res = $this->transformer->reverseTransform('/asd');
        $this->assertSame($this->document, $res);
    }

    public function testReverseTransformEmpty(): void
    {
        $this->dm->expects($this->never())
            ->method('find');
        $res = $this->transformer->reverseTransform('');
        $this->assertNull($res);
    }
}
