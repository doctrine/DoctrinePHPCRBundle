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

use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;

class DocumentType extends DoctrineType
{
    public function getLoader(ObjectManager $manager, object $queryBuilder, string $class): EntityLoaderInterface
    {
        if (!$manager instanceof DocumentManagerInterface || !($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new \InvalidArgumentException('Expected a '.DocumentManagerInterface::class.' and a closure or '.QueryBuilder::class.', got '.get_class($manager).' and '.get_class($queryBuilder));
        }

        return new PhpcrOdmQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    public function getBlockPrefix(): string
    {
        return 'phpcr_document';
    }
}
