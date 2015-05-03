<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Jackalope\Node;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;

class PathTypeTest extends \PHPUnit_Framework_Testcase
{
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
                return null;
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

