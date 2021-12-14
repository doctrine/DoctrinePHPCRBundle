<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathTypeTest extends Testcase
{
    /**
     * @var ManagerRegistry|MockObject
     */
    private $reg;

    /**
     * @var DocumentManager|MockObject
     */
    private $dm;

    /**
     * @var FormBuilder|MockObject
     */
    private $builder;

    /**
     * @var OptionsResolver|MockObject
     */
    private $optionsResolver;

    /**
     * @var PathType
     */
    private $type;

    public function setUp(): void
    {
        $this->reg = $this->createMock(ManagerRegistry::class);

        $this->dm = $this->createMock(DocumentManager::class);

        $this->builder = $this->createMock(FormBuilder::class);
        $this->optionsResolver = $this->createMock(OptionsResolver::class);
        $this->type = new PathType($this->reg);
    }

    public function testBuildForm()
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
