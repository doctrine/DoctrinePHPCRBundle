<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Jackalope\Node;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;

class PathTypeTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new PathType($this->dm);
    }

    public function testBuildForm()
    {
        $test = $this;
        $this->builder->expects($this->once())
            ->method('addModelTransformer')
            ->will($this->returnCallback(function ($transformer) use ($test) {
                $test->assertInstanceOf(
                    'Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer',
                    $transformer
                );
                return null;
            }));

        $this->type->buildForm($this->builder, array());
    }
}

