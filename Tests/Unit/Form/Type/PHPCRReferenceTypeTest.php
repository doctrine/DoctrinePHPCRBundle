<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\Type\PHPCRReferenceType;

class PHPCRReferenceTypeTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->session = $this->getMock('PHPCR\SessionInterface');

        // hmm, phpunit won't mock a traversable interface so mocking the concrete class
        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new PHPCRReferenceType($this->session);
    }

    public function provideTypes()
    {
        return array(
            array('uuid', 'Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer'),
            array('path', 'Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer'),
        );
    }

    /**
     * @dataProvider provideTypes
     */
    public function testBuildForm($transformerType, $transformerFqn)
    {
        $type = null;
        $this->builder->expects($this->once())
            ->method('addModelTransformer')
            ->will($this->returnCallback(function ($transformer) use (&$type) {
                $type = get_class($transformer);

                return;
            }));
        $this->type->buildForm($this->builder, array('transformer_type' => $transformerType));

        $this->assertEquals($transformerFqn, $type);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidType()
    {
        $this->type->buildForm($this->builder, array('transformer_type' => 'asdasd'));
    }
}
