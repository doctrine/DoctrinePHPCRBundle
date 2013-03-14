<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\ModelToFileTransformer;

class ImageType extends AbstractType
{
    private $defaultFilter;

    public function __construct($defaultFilter)
    {
        $this->defaultFilter = $defaultFilter;
    }

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

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['imagine_filter'] = $options['imagine_filter'];
    }

    public function setDefaultOptions(OptionsResolverInterface $options)
    {
        $options->setDefaults(array(
            'data_class' => 'Doctrine\ODM\PHPCR\Document\Image',
            'imagine_filter' => $this->defaultFilter,
        ));
    }
}
