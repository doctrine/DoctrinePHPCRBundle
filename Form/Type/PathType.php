<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;
use Doctrine\Common\Persistence\ManagerRegistry;

class PathType extends AbstractType
{
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
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
        $resolver->setDefaults(array(
            'manager_name' => null,
        ));
    }
}
