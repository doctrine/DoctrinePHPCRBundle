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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends DoctrineType
{
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
//
//        $resolver->setDefaults(array(
//            'choice_value' => 'uuid'
//        ));

//        // Set the "id_reader" option via the normalizer. This option is not
//        // supposed to be set by the user.
//        $idReaderNormalizer = function (Options $options) use (&$idReaders) {
//            $hash = CachingFactoryDecorator::generateHash(array(
//                $options['em'],
//                $options['class'],
//            ));
//
//            // The ID reader is a utility that is needed to read the object IDs
//            // when generating the field values. The callback generating the
//            // field values has no access to the object manager or the class
//            // of the field, so we store that information in the reader.
//            // The reader is cached so that two choice lists for the same class
//            // (and hence with the same reader) can successfully be cached.
//            if (!isset($idReaders[$hash])) {
//                $classMetadata = $options['em']->getClassMetadata($options['class']);
//                $idReaders[$hash] = new IdReader($options['em'], $classMetadata);
//            }
//
//            return $idReaders[$hash];
//        };
//
//        $resolver->setNormalizer('id_reader', $idReaderNormalizer);
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
