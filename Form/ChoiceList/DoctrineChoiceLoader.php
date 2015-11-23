<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader as BaseDoctrineChoiceLoader;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata as PHPCRClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PHPCR\Util\UUIDHelper;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Supports UUIDs as choice values, it will automatically detect them and if the PHPCR document
 * has a UUID field mapping it will be enabled.
 *
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class DoctrineChoiceLoader extends BaseDoctrineChoiceLoader
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var IdReader
     */
    private $idReader;

    /**
     * @var null|EntityLoaderInterface
     */
    private $objectLoader;

    /**
     * @var ChoiceListInterface
     */
    private $choiceList;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * {@inheritdoc}
     */
    public function __construct(ChoiceListFactoryInterface $factory, ObjectManager $manager, $class, IdReader $idReader = null, EntityLoaderInterface $objectLoader = null)
    {
        parent::__construct($factory, $manager, $class, $idReader, $objectLoader);

        $classMetadata = $manager->getClassMetadata($class);

        $this->manager = $manager;
        $this->classMetadata = $classMetadata;
        $this->idReader = $idReader ?: new IdReader($manager, $classMetadata);
        $this->objectLoader = $objectLoader;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Performance optimization
        // Also prevents the generation of "WHERE id IN ()" queries through the
        // object loader. At least with MySQL and on the development machine
        // this was tested on, no exception was thrown for such invalid
        // statements, consequently no test fails when this code is removed.
        // https://github.com/symfony/symfony/pull/8981#issuecomment-24230557
        if (empty($values)) {
            return array();
        }

        $uuidFieldName = null;
        if ($this->classMetadata instanceof PHPCRClassMetadata) {
            if ($this->classMetadata->referenceable) {
                $uuidFieldName = $this->classMetadata->getUuidFieldName();
            }
        }

        // Optimize performance in case we have an object loader and
        // a single-field identifier
        if (!$this->choiceList && $this->objectLoader && $this->idReader->isSingleId()) {
            $unorderedObjects = $this->objectLoader->getEntitiesByIds($this->idReader->getIdField(), $values);
            $objectsById = array();
            $objects = array();

            // Maintain order and indices from the given $values
            // An alternative approach to the following loop is to add the
            // "INDEX BY" clause to the Doctrine query in the loader,
            // but I'm not sure whether that's doable in a generic fashion.
            foreach ($unorderedObjects as $object) {
                $objectsById[$this->idReader->getIdValue($object)] = $object;
            }

            foreach ($values as $i => $id) {
                if (UUIDHelper::isUUID($id) && null !== $uuidFieldName) {
                    foreach ($unorderedObjects as $object) {
                        if ($id === $this->propertyAccessor->getValue($object, $uuidFieldName)) {
                            $objects[$i] = $object;
                            break;
                        }
                    }
                } elseif (isset($objectsById[$id])) {
                    $objects[$i] = $objectsById[$id];
                }
            }

            return $objects;
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }
}
