<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\DocumentToPathTransformer;

class PathType extends AbstractType
{
    protected $dm;

    public function __construct($dm)
    {
        $this->dm = $dm;
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
        $transformer = new DocumentToPathTransformer($this->dm);
        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    public function setDefaultOptions(OptionsResolverInterface $options)
    {
    }
}

