<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PHPCRReferenceType;
use PHPCR\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilder;

class PHPCRReferenceTypeTest extends Testcase
{
    /**
     * @var FormBuilder&MockObject
     */
    private FormBuilder $builder;
    private PHPCRReferenceType $type;

    public function setUp(): void
    {
        $session = $this->createMock(SessionInterface::class);

        // hmm, phpunit won't mock a traversable interface so mocking the concrete class
        $this->builder = $this->createMock(FormBuilder::class);
        $this->type = new PHPCRReferenceType($session);
    }

    public function provideTypes(): array
    {
        return [
            ['uuid', PHPCRNodeToUuidTransformer::class],
            ['path', PHPCRNodeToPathTransformer::class],
        ];
    }

    /**
     * @dataProvider provideTypes
     */
    public function testBuildForm(string $transformerType, string $transformerFqn): void
    {
        $type = null;
        $this->builder->expects($this->once())
            ->method('addModelTransformer')
            ->willReturnCallback(function ($transformer) use (&$type) {
                $type = \get_class($transformer);

                return $this->builder;
            });
        $this->type->buildForm($this->builder, ['transformer_type' => $transformerType]);

        $this->assertEquals($transformerFqn, $type);
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->type->buildForm($this->builder, ['transformer_type' => 'asdasd']);
    }
}
