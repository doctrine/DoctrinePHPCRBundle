<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\ModelToFileTransformer;

class ImageType extends AbstractType
{
    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'phpcr_odm_image';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelToFileTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $options)
    {
        $options->setDefaults(array('data_class' => 'Doctrine\ODM\PHPCR\Document\Image'));
    }
}
