<?php

namespace Doctrine\Bundle\PHPCRBundle\Validator\Constraints;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
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
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object     $document
     * @param Constraint $constraint
     */
    public function validate($document, Constraint $constraint)
    {
        $className = get_class($document);
        $dm = $this->registry->getManagerForClass($className);

        if (null === $dm) {
            throw new ConstraintDefinitionException('This document is not managed by the PHPCR ODM.');
        }

        /** @var ClassMetadata $class */
        $class = $dm->getClassMetadata($className);

        if ($class->getFieldValue($document, $class->identifier)) {
            return;
        }

        $parent = $class->getFieldValue($document, $class->parentMapping);

        if (empty($parent)) {
            $this->legacyAddViolationAt($class->parentMapping, $constraint->message);
        }

        $name = $class->getFieldValue($document, $class->nodename);

        if (empty($name)) {
            $this->legacyAddViolationAt($class->nodename, $constraint->message);
        }
    }

    private function legacyAddViolationAt($property, $message)
    {
        if (method_exists($this->context, 'buildViolation')) {
            // Symfony Validator 2.5+ API
            $this->context->buildViolation($message)
                ->atPath($property)
                ->addViolation();
        } else {
            // Symfony Validator <2.5 API
            $this->context->addViolationAt($property, $message);
        }
    }
}
