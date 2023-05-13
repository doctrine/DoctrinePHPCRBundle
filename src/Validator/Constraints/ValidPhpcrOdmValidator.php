<?php

namespace Doctrine\Bundle\PHPCRBundle\Validator\Constraints;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Validator to check if a document has mappings for either an identifier or both a parent and a name.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ValidPhpcrOdmValidator extends ConstraintValidator
{
    private ManagerRegistryInterface $registry;

    public function __construct(ManagerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $document
     */
    public function validate($document, Constraint $constraint): void
    {
        $className = \get_class($document);
        $dm = $this->registry->getManagerForClass($className);

        if (!$dm instanceof DocumentManagerInterface) {
            throw new ConstraintDefinitionException('This document is not managed by the PHPCR ODM.');
        }

        $classMetadata = $dm->getClassMetadata($className);

        if ($classMetadata->getFieldValue($document, $classMetadata->identifier)) {
            return;
        }

        $parent = $classMetadata->getFieldValue($document, $classMetadata->parentMapping);

        if (empty($parent)) {
            $this->context->buildViolation($constraint->message)
                ->atPath($classMetadata->parentMapping)
                ->addViolation();
        }

        $name = $classMetadata->getFieldValue($document, $classMetadata->nodename);

        if (empty($name)) {
            $this->context->buildViolation($constraint->message)
                ->atPath($classMetadata->nodename)
                ->addViolation();
        }
    }
}
