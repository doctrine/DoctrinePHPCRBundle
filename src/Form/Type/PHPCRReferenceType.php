<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToUuidTransformer;
use PHPCR\SessionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for PHPCR Node references.
 *
 * Can use either a UUID or a PATH transformer as specified by the "transfomer_type" option.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PHPCRReferenceType extends AbstractType
{
    private ?SessionInterface $session;

    public function __construct(SessionInterface $session = null)
    {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        switch (strtolower($options['transformer_type'])) {
            case 'uuid':
                $transformer = new PHPCRNodeToUuidTransformer($this->session);

                break;
            case 'path':
                $transformer = new PHPCRNodeToPathTransformer($this->session);

                break;
            default:
                throw new InvalidConfigurationException(sprintf('
                    The option "transformer_type" must be either "uuid" or "path", "%s" given',
                    $options['transformer_type']
                ));
        }

        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'transformer_type' => 'uuid',
        ]);
    }

    public function getParent(): string
    {
        return method_exists(AbstractType::class, 'getBlockPrefix') ? TextType::class : 'text';
    }

    public function getBlockPrefix(): string
    {
        return 'phpcr_reference';
    }
}
