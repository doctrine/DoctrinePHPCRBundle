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
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\Form\Exception\RuntimeException;

class DocumentType extends DoctrineType
{
    /**
     * {@inheritDoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        // TODO: check if phpcr-odm query builder can work with the form component and return it
        throw new RuntimeException('The query builder option is not supported by PHPCR.');
    }

    public function getName()
    {
        return 'phpcr_document';
    }
}
