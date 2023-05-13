<?php

namespace Doctrine\Bundle\PHPCRBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Valid PHPCR ODM validator.
 *
 * @Annotation
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ValidPhpcrOdm extends Constraint
{
    public string $message = 'This value should not be blank.';

    public string $service = 'doctrine_phpcr.odm.validator.valid_phpcr_odm';

    /**
     * The validator must be defined as a service with this name.
     */
    public function validatedBy(): string
    {
        return $this->service;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
