<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\ODM\PHPCR\Query\QueryBuilder;
use Doctrine\PHPCR\Connection;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * Getting Documents through the PHPCR-ODM QueryBuilder
 */
class PHPCRODMQueryBuilderLoader implements EntityLoaderInterface
{
    /**
     * Contains the query builder that builds the query for fetching the
     * documents
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder $queryBuilder
     */
    private $queryBuilder;

    /**
     * Construct an PHPCRODM Query Builder Loader
     *
     * @param QueryBuilder|\Closure $queryBuilder
     * @param DocumentManager       $manager
     * @param string                $class
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($queryBuilder, $manager = null, $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntities()
    {
        return $this->queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $qb = clone ($this->queryBuilder);
        $factory = $qb->getQOMFactory();

        foreach ($values as $value) {
            $qb->orWhere($factory->sameNode($value));
        }

        return $qb->getQuery()->getResult();
    }
}
