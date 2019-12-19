<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PHPCRReferenceType;
use PHPCR\SessionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;

class PHPCRReferenceTypeTest extends Testcase
{
    public function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);

        // hmm, phpunit won't mock a traversable interface so mocking the concrete class
        $this->builder = $this->createMock(FormBuilder::class);
        $this->type = new PHPCRReferenceType($this->session);
    }

    public function provideTypes()
    {
        return [
            ['uuid', PHPCRNodeToUuidTransformer::class],
            ['path', PHPCRNodeToPathTransformer::class],
        ];
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
        $this->type->buildForm($this->builder, ['transformer_type' => $transformerType]);

        $this->assertEquals($transformerFqn, $type);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidType()
    {
        $this->type->buildForm($this->builder, ['transformer_type' => 'asdasd']);
    }
}
