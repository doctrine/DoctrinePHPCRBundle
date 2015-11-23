<?php

/*
 * Doctrine PHPCR-ODM Bundle
 *
 * (Ported from Doctrine CouchDB Bundle)
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Doctrine\Bundle\PHPCRBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;

class DocumentType extends DoctrineType
{
    private $choiceListFactory;

    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = array();

    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor = null, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        parent::__construct($registry, $propertyAccessor, $choiceListFactory);
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new DefaultChoiceListFactory(), $propertyAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new PhpcrOdmQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $choiceListFactory = $this->choiceListFactory;
        $choiceLoaders = &$this->choiceLoaders;
        $type = $this;

        // we need to override the default choiceLoader, since we want to use our own class
        $choiceLoader = function (Options $options) use ($choiceListFactory, &$choiceLoaders, $type) {
            // Unless the choices are given explicitly, load them on demand
            if (null === $options['choices']) {
                $hash = null;
                $qbParts = null;

                // If there is no QueryBuilder we can safely cache DoctrineChoiceLoader,
                // also if concrete Type can return important QueryBuilder parts to generate
                // hash key we go for it as well
                if (!$options['query_builder'] || false !== ($qbParts = $type->getQueryBuilderPartsForCachingHash($options['query_builder']))) {
                    $hash = CachingFactoryDecorator::generateHash(array(
                        $options['em'],
                        $options['class'],
                        $qbParts,
                        $options['loader'],
                    ));

                    if (isset($choiceLoaders[$hash])) {
                        return $choiceLoaders[$hash];
                    }
                }

                if ($options['loader']) {
                    $entityLoader = $options['loader'];
                } elseif (null !== $options['query_builder']) {
                    $entityLoader = $type->getLoader($options['em'], $options['query_builder'], $options['class']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $type->getLoader($options['em'], $queryBuilder, $options['class']);
                }

                // this line is different from the original choice loader, we use our own DoctrineChoiceLoader,
                // were we have our changes to support UUIDs
                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $choiceListFactory,
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                if ($hash !== null) {
                    $choiceLoaders[$hash] = $doctrineChoiceLoader;
                }

                return $doctrineChoiceLoader;
            }
        };

        $resolver->setDefaults(array(
            'choice_loader' => $choiceLoader,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'phpcr_document';
    }
}
