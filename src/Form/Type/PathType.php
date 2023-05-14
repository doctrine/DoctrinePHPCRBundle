<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathType extends AbstractType
{
    private ManagerRegistryInterface $registry;

    public function __construct(ManagerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function getParent(): string
    {
        return method_exists(AbstractType::class, 'getBlockPrefix') ? TextType::class : 'text';
    }

    public function getBlockPrefix(): string
    {
        return 'phpcr_odm_path';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dm = $this->registry->getManager($options['manager_name']);
        $transformer = new DocumentToPathTransformer($dm);
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'manager_name' => null,
        ]);
    }
}
