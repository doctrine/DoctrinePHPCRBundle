<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

class PathType extends AbstractType
{
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\TextType';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        // As of Symfony 2.8, the name defaults to the fully-qualified class name
        return get_class($this);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'phpcr_odm_path';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dm = $this->registry->getManager($options['manager_name']);
        $transformer = new DocumentToPathTransformer($dm);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'manager_name' => null,
        ));
    }
}
