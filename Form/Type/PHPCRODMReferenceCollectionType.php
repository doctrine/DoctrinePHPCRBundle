<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\ReferenceManyCollectionToArrayTransformer;

/**
 * A type to handle a list of references as simple choice.
 */
class PHPCRODMReferenceCollectionType extends AbstractType
{

    protected $dm;

    /**
     * @param DocumentManager $dm
     */
    function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'phpcr_odm_reference_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired((array('referenced_class')));
        $resolver->setOptional(array('key'));

        $resolver->setDefaults(array(
            'key' => ReferenceManyCollectionToArrayTransformer::KEY_UUID,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ReferenceManyCollectionToArrayTransformer($this->dm, $options['referenced_class'], $options['key']);
        $builder->addModelTransformer($transformer);
    }
}
