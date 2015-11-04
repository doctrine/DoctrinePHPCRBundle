<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathTypeTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reg;

    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dm;

    /**
     * @var FormBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsResolver;

    /**
     * @var PathType
     */
    private $type;

    public function setUp()
    {
        $this->reg = $this->getMockBuilder('Doctrine\Bundle\PHPCRBundle\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionsResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
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
                    'Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer',
                    $transformer
                );

                return;
            }));

        $this->type->buildForm($this->builder, array('manager_name' => null));
    }

    public function testSetDefaultOptions()
    {
        $this->optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('manager_name' => null));
        $this->type->setDefaultOptions($this->optionsResolver);
    }
}
