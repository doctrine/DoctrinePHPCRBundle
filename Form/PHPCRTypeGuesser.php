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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

/**
 * Guesser for Form component using Doctrine phpcr registry and metadata.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class PHPCRTypeGuesser implements FormTypeGuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    private $cache = array();

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }

        list($metadata, $documentManager) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            return new TypeGuess('phpcr_document', array(
                'dm' => $documentManager,
                'class' => $mapping['targetDocument'],
                'multiple' => $multiple
                ),
                Guess::HIGH_CONFIDENCE
            );
        }

        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                return new TypeGuess('checkbox', array(), Guess::HIGH_CONFIDENCE);
            case 'binary':
                return new TypeGuess('file', array(), Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess('datetime', array(), Guess::HIGH_CONFIDENCE);
            case 'double':
                return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);
            case 'long':
            case 'integer':
                return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess('text', array(), Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function guessMinLength($class, $property)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
    }

    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $manager = $this->registry->getManagerForClass($class);
        if ($manager) {
            return $this->cache[$class] = array($manager->getClassMetadata($class), $manager);
        }
        return $this->cache[$class] = null;
    }
}
