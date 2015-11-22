<?php
/**
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Form\ChoiceList;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader as BaseIdReader;
use Symfony\Component\PropertyAccess\PropertyAccess;

class IdReader extends BaseIdReader
{
    protected $idField;

    /**
     * @param ObjectManager $om
     * @param ClassMetadata $classMetadata
     * @param string        $idField
     */
    public function __construct(ObjectManager $om, ClassMetadata $classMetadata, $idField = null)
    {
        parent::__construct($om, $classMetadata);
        $this->idField = $idField;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getIdValue($object)
    {
        if ($this->idField !== null) {
            if ($this->propertyAccessor->isReadable($object, $this->idField)) {
                return $this->propertyAccessor->getValue($object, $this->idField);
            }
        }

        return parent::getIdValue($object);
    }

    public function getIdField()
    {
        if ($this->idField !== null) {
            return $this->idField;
        }

        return parent::getIdField();
    }
}