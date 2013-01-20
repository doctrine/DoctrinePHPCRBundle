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

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\QueryBuilder;

/**
 * Getting Documents through the PHPCR ODM QueryBuilder
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class PHPCRQueryBuilderLoader implements EntityLoaderInterface
{
    /**
     * @var Doctrine\ODM\PHPCR\DocumentManager
     */
    private $manager;

    /**
     * @var string
     */
    private $class;

    /**
     * @param QueryBuilder|\Closure $queryBuilder
     * @param DocumentManager         $manager
     * @param string                $class
     */
    public function __construct($queryBuilder, $manager, $class = null)
    {
        $this->manager = $manager;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntities()
    {
        // TODO add support for query builder
        return $this->manager->getRepository($this->class)->createQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        $this->manager->findMany($this->class, $values);
    }
}
