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

namespace Doctrine\Bundle\PHPCRBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

/**
 * Guesser for Form component using Doctrine phpcr registry and metadata.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class PhpcrOdmTypeGuesser implements FormTypeGuesserInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var string
     *
     * guessed form types
     */
    private $typeGuess = array();

    private $cache = array();

    public function __construct(ManagerRegistry $registry, $typeGuess = array())
    {
        $this->registry = $registry;
        $this->typeGuess = $typeGuess;
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }

        /** @var ClassMetadata $metadata */
        /** @var DocumentManager $documentManager */
        list($metadata, $documentManager) = $ret;

        if ($metadata->hasAssociation($property)) {
            $mapping = $metadata->getAssociation($property);

            switch ($mapping['type']) {
                case 'parent':
                    return new TypeGuess('phpcr_odm_path', array(), Guess::MEDIUM_CONFIDENCE);

                case 'mixedreferrers':
                    $options = array(
                        'read_only' => true,
                        'type' => 'phpcr_odm_path',
                    );

                    return new TypeGuess('collection', $options, Guess::LOW_CONFIDENCE);

                case 'referrers':
                    return new TypeGuess('phpcr_document', array(
                            'class' => $mapping['referringDocument'],
                            'multiple' => true,
                        ),
                        Guess::HIGH_CONFIDENCE
                    );

                case ClassMetadata::MANY_TO_MANY:
                case ClassMetadata::MANY_TO_ONE:
                    $options = array(
                        'multiple' => $metadata->isCollectionValuedAssociation($property),
                    );
                    if (isset($mapping['targetDocument'])) {
                        $options['class'] = $mapping['targetDocument'];
                    }

                    return new TypeGuess('phpcr_document', $options, Guess::HIGH_CONFIDENCE);

                case 'child':
                    $options = array(
                        'read_only' => true,
                    );

                    return new TypeGuess('phpcr_odm_path', $options, Guess::LOW_CONFIDENCE);

                case 'children':
                    $options = array(
                        'read_only' => true,
                        'type' => 'phpcr_odm_path',
                    );

                    return new TypeGuess('collection', $options, Guess::LOW_CONFIDENCE);

                default:
                    return;
            }
        }

        $mapping = $metadata->getField($property);

        if (!empty($mapping['assoc'])) {
            if (isset($this->typeGuess['assoc'])) {
                list($type, $options) = each($this->typeGuess['assoc']);

                return new TypeGuess($type, $options, Guess::MEDIUM_CONFIDENCE);
            }

            return;
        }

        $options = array();
        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                $type = 'checkbox';
                break;
            case 'binary':
                // the file type only works on documents like the File document,
                // not directly on properties with raw binary data.
                return;
            case 'node':
                // editing the phpcr node has no meaning
                return;
            case 'date':
                $type = 'datetime';
                break;
            case 'double':
                $type = 'number';
                break;
            case 'long':
            case 'integer':
                $type = 'integer';
                break;
            case 'string':
                if ($metadata->isIdentifier($property)
                    || $metadata->isUuid($property)
                ) {
                    $options['read_only'] = true;
                }
                $type = 'text';
                break;
            case 'nodename':
                $type = 'text';
                break;
            case 'locale':
                $locales = $documentManager->getLocaleChooserStrategy();
                $type = 'choice';
                $options['choices'] = array_combine($locales->getDefaultLocalesOrder(), $locales->getDefaultLocalesOrder());
                break;
            case 'versionname':
            case 'versioncreated':
            default:
                $options['read_only'] = true;
                $options['required'] = false;
                $type = 'text';
                break;
        }

        if (!empty($mapping['multivalue'])) {
            $options['type'] = $type;
            $type = 'collection';
        }

        if (!empty($mapping['translated'])) {
            $options['attr'] = array('class' => 'translated');
        }

        return new TypeGuess($type, $options, Guess::HIGH_CONFIDENCE);
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function guessMinLength($class, $property)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
        /** @var ClassMetadata $metadata */
        list($metadata, $documentManager) = $this->getMetadata($class);

        if (!$metadata) {
            return;
        }

        if ($metadata->hasField($property)) {
            if (!$metadata->isNullable($property)
                && 'boolean' !== $metadata->getTypeOfField($property)
                && !$metadata->isUuid($property)
            ) {
                $required = true;
                if (ClassMetadata::GENERATOR_TYPE_ASSIGNED !== $metadata->idGenerator && $metadata->isIdentifier($property)
                    || ClassMetadata::GENERATOR_TYPE_PARENT !== $metadata->idGenerator && $property === $metadata->nodename
                ) {
                    $required = false;
                }

                return new ValueGuess($required, Guess::HIGH_CONFIDENCE);
            }

            return new ValueGuess(false, Guess::MEDIUM_CONFIDENCE);
        }

        if ($metadata->hasAssociation($property)) {
            if ($property === $metadata->parentMapping
                && ClassMetadata::GENERATOR_TYPE_ASSIGNED !== $metadata->idGenerator
            ) {
                return new ValueGuess(true, Guess::HIGH_CONFIDENCE);
            }

            return new ValueGuess(false, Guess::LOW_CONFIDENCE);
        }

        return;
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
        return;
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
