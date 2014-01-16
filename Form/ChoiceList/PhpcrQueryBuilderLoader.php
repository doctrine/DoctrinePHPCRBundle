<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PhpcrQueryBuilderLoader implements EntityLoaderInterface
{

    /**
     * Contains the query builder that builds the query for fetching the
     * entities
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Construct an ORM Query Builder Loader
     *
     * @param QueryBuilder|\Closure $queryBuilder
     * @param ObjectManager $manager
     * @param string $class
     */
    public function __construct($queryBuilder, ObjectManager $manager = null, $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\Common\Persistence\ObjectManager or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Doctrine\Common\Persistence\ObjectManager');
            }
        }

        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return array The entities.
     */
    public function getEntities()
    {
        return array_values($this->queryBuilder->getQuery()->execute()->toArray());
    }

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array $values The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $qb = $this->queryBuilder;
        foreach($values as $val) {
            $qb->orWhere()->eq()->field($qb->getPrimaryAlias().'.'.$identifier)->literal($val);
        }
        return array_values($qb->getQuery()->execute()->toArray());
    }


}
