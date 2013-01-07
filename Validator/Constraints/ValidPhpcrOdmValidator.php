<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\Validator\Constraints;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Valid PHPCR ODM Validator checks if a document has an identifier or a parent and a name
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ValidPhpcrOdmValidator extends ConstraintValidator
{
    protected $registry;

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

        $class = $dm->getClassMetadata($className);

        if ($class->getFieldValue($document, $class->identifier)) {
            return;
        }

        $parent = $class->getFieldValue($document, $class->parentMapping['fieldName']);

        if (empty($parent)) {
            $this->context->addViolationAtSubPath($class->parentMapping['fieldName'], $constraint->message);
        }

        $name = $class->getFieldValue($document, $class->nodename);

        if (empty($name)) {
            $this->context->addViolationAtSubPath($class->nodename, $constraint->message);
        }
    }
}
