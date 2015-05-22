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

class DocumentType extends DoctrineType
{
    /**
     * {@inheritDoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new PhpcrOdmQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    public function getName()
    {
        return 'phpcr_document';
    }
}
