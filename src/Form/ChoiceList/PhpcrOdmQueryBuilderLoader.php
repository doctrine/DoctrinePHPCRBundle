<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Getting Documents through the PHPCR ODM QueryBuilder.
 *
 * This class is using a misnamed interface from the symfony doctrine bridge,
 * hence the use of "entity" instead of the "object".
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Ivan Borzenkov <ivan.borzenkov@gmail.com>
 */
final class PhpcrOdmQueryBuilderLoader implements EntityLoaderInterface
{
    private QueryBuilder $queryBuilder;

    /**
     * @param QueryBuilder|\Closure $queryBuilder
     */
    public function __construct($queryBuilder, DocumentManagerInterface $manager = null, string $class = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder
        // instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, ObjectManager::class.' or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            if (null === $manager) {
                throw new \InvalidArgumentException('Can not use a closure for the query builder when no document manager has been specified');
            }
            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, ObjectManager::class);
            }
        }

        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns an array of documents that are valid choices in the
     * corresponding choice list.
     *
     * @return array the documents
     */
    public function getEntities(): array
    {
        return $this->getResult($this->queryBuilder);
    }

    /**
     * Returns an array of documents matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array  $values     the values of the identifiers
     *
     * @return array the entities
     */
    public function getEntitiesByIds(string $identifier, array $values): array
    {
        $values = array_values(array_filter($values, static function ($v) {
            return !empty($v);
        }));

        if (0 === \count($values)) {
            return [];
        }

        /* performance: if we could figure out whether the query builder is
         * "empty" (that is only checking for the class) we could optimize this
         * to a $this->dm->findMany(null, $values)
         */

        $qb = clone $this->queryBuilder;
        $alias = $qb->getPrimaryAlias();
        $where = $qb->andWhere()->orX();
        foreach ($values as $val) {
            $where->same($val, $alias);
        }

        return $this->getResult($qb);
    }

    /**
     * Run the query and return a consecutive array.
     *
     * @return array list of result documents
     */
    private function getResult(QueryBuilder $qb): array
    {
        return array_values($qb->getQuery()->execute()->toArray());
    }
}
