<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\ReferenceManyCollectionToArrayTransformer;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * A type to handle a list of references as simple choice.
 *
 * @deprecated This seems to do nothing more than the DocumentType.
 *      Will be removed in 1.2.
 */
class PHPCRODMReferenceCollectionType extends AbstractType
{
    protected $dm;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
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
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['referenced_class']);

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setDefined('key');
        } else {
            // todo: remove when Symfony <2.6 support is dropped
            $resolver->setOptional(['key']);
        }

        $resolver->setDefaults([
            'key' => ReferenceManyCollectionToArrayTransformer::KEY_UUID,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        @trigger_error('This form type is deprecated in favor of phpcr_document. If you think this is an error, please contact us and explain. We were not able to figure out what this type is good for.', E_USER_DEPRECATED);

        $transformer = new ReferenceManyCollectionToArrayTransformer($this->dm, $options['referenced_class'], $options['key']);
        $builder->addModelTransformer($transformer);
    }
}
