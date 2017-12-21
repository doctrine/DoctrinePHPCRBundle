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
    public $message = 'This value should not be blank.';
    public $service = 'doctrine_phpcr.odm.validator.valid_phpcr_odm';

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
