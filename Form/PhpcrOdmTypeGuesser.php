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

    /**
     * Work with 2.3-2.7 and 3.0 at the same time. drop once we switch to symfony 3.0.
     */
    private $legacy = true;

    /**
     * Work with 2.3-2.7 and 3.0 at the same time. drop once we switch to symfony 3.0.
     */
    private $entryTypeOption = 'type';

    public function __construct(ManagerRegistry $registry, $typeGuess = array())
    {
        $this->registry = $registry;
        $this->typeGuess = $typeGuess;
        $this->legacy = !method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
        $this->entryTypeOption = $this->legacy ? 'type' : 'entry_type';
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess($this->legacy ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType', array(), Guess::LOW_CONFIDENCE);
        }

        /** @var ClassMetadata $metadata */
        /** @var DocumentManager $documentManager */
        list($metadata, $documentManager) = $ret;

        if ($metadata->hasAssociation($property)) {
            $mapping = $metadata->getAssociation($property);

            switch ($mapping['type']) {
                case 'parent':
                    return new TypeGuess($this->legacy ? 'phpcr_odm_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType', array(), Guess::MEDIUM_CONFIDENCE);

                case 'mixedreferrers':
                    $options = array(
                        'attr' => array('readonly' => 'readonly'),
                        $this->entryTypeOption => $this->legacy ? 'phpcr_odm_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
                    );

                    return new TypeGuess($this->legacy ? 'collection' : 'Symfony\Component\Form\Extension\Core\Type\CollectionType', $options, Guess::LOW_CONFIDENCE);

                case 'referrers':
                    return new TypeGuess($this->legacy ? 'phpcr_document' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType', array(
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

                    return new TypeGuess($this->legacy ? 'phpcr_document' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType', $options, Guess::HIGH_CONFIDENCE);

                case 'child':
                    $options = array(
                        'attr' => array('readonly' => 'readonly'),
                    );

                    return new TypeGuess($this->legacy ? 'phpcr_odm_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType', $options, Guess::LOW_CONFIDENCE);

                case 'children':
                    $options = array(
                        'attr' => array('readonly' => 'readonly'),
                        $this->entryTypeOption => $this->legacy ? 'phpcr_odm_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
                    );

                    return new TypeGuess($this->legacy ? 'collection' : 'Symfony\Component\Form\Extension\Core\Type\CollectionType', $options, Guess::LOW_CONFIDENCE);

                default:
                    return;
            }
        }

        $mapping = $metadata->getFieldMapping($property);

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
                $type = $this->legacy ? 'checkbox' : 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';

                break;
            case 'binary':
                // the file type only works on documents like the File document,
                // not directly on properties with raw binary data.
                return;
            case 'node':
                // editing the phpcr node has no meaning
                return;
            case 'date':
                $type = $this->legacy ? 'datetime' : 'Symfony\Component\Form\Extension\Core\Type\DateTimeType';

                break;
            case 'double':
                $type = $this->legacy ? 'number' : 'Symfony\Component\Form\Extension\Core\Type\NumberType';

                break;
            case 'long':
            case 'integer':
                $type = $this->legacy ? 'integer' : 'Symfony\Component\Form\Extension\Core\Type\IntegerType';

                break;
            case 'string':
                if ($metadata->isIdentifier($property)
                    || $metadata->isUuid($property)
                ) {
                    $options['attr'] = array('readonly' => 'readonly');
                }
                $type = $this->legacy ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType';

                break;
            case 'nodename':
                $type = $this->legacy ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType';

                break;
            case 'locale':
                $locales = $documentManager->getLocaleChooserStrategy();
                $type = $this->legacy ? 'choice' : 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
                $options['choices'] = array_combine($locales->getDefaultLocalesOrder(), $locales->getDefaultLocalesOrder());

                break;
            case 'versionname':
            case 'versioncreated':
            default:
                $options['attr'] = array('readonly' => 'readonly');
                $options['required'] = false;
                $type = $this->legacy ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType';

                break;
        }

        if (!empty($mapping['multivalue'])) {
            $options[$this->entryTypeOption] = $type;
            $type = $this->legacy ? 'collection' : 'Symfony\Component\Form\Extension\Core\Type\CollectionType';
        }

        if (!empty($mapping['translated'])) {
            $options['attr'] = array('class' => 'translated');
        }

        return new TypeGuess($type, $options, Guess::HIGH_CONFIDENCE);
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function guessMinLength($class, $property)
    {
        return;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
