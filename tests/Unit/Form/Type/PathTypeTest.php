<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;

class PathTypeTest extends Testcase
{
    /**
     * @var ManagerRegistryInterface&MockObject
     */
    private ManagerRegistryInterface $reg;

    /**
     * @var DocumentManagerInterface&MockObject
     */
    private DocumentManagerInterface $dm;

    /**
     * @var FormBuilder&MockObject
     */
    private FormBuilder $builder;

    private PathType $type;

    public function setUp(): void
    {
        $this->reg = $this->createMock(ManagerRegistryInterface::class);

        $this->dm = $this->createMock(DocumentManager::class);

        $this->builder = $this->createMock(FormBuilder::class);
        $this->type = new PathType($this->reg);
    }

    public function testBuildForm(): void
    {
        $test = $this;
        $this->reg->expects($this->once())
            ->method('getManager')
            ->with(null)
            ->will($this->returnValue($this->dm));
        $this->builder->expects($this->once())
            ->method('addModelTransformer')
            ->will($this->returnCallback(function ($transformer) use ($test) {
                $test->assertInstanceOf(
                    DocumentToPathTransformer::class,
                    $transformer
                );

                return $this->builder;
            }));

        $this->type->buildForm($this->builder, ['manager_name' => null]);
    }
}
