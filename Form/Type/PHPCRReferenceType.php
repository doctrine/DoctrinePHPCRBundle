<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use PHPCR\SessionInterface;

class PHPCRReferenceType extends AbstractType
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new PHPCRNodeToPathTransformer($this->session);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'phpcr_reference';
    }
}
