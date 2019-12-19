<?php

namespace Doctrine\Bundle\PHPCRBundle\Form;

use Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

/**
 * Guesser for Form component using Doctrine PHPCR registry and metadata.
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
    private $typeGuess = [];

    private $cache = [];

    public function __construct(ManagerRegistry $registry, $typeGuess = [])
    {
        $this->registry = $registry;
        $this->typeGuess = $typeGuess;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
        }

        /** @var ClassMetadata $metadata */
        /** @var DocumentManager $documentManager */
        list($metadata, $documentManager) = $ret;

        if ($metadata->hasAssociation($property)) {
            $mapping = $metadata->getAssociation($property);

            switch ($mapping['type']) {
                case 'parent':
                    return new TypeGuess(PathType::class, [], Guess::MEDIUM_CONFIDENCE);

                case 'mixedreferrers':
                    $options = [
                        'attr' => ['readonly' => 'readonly'],
                        'entry_type' => PathType::class,
                    ];

                    return new TypeGuess(CollectionType::class, $options, Guess::LOW_CONFIDENCE);

                case 'referrers':
                    return new TypeGuess(DocumentType::class, [
                            'class' => $mapping['referringDocument'],
                            'multiple' => true,
                        ],
                        Guess::HIGH_CONFIDENCE
                    );

                case ClassMetadata::MANY_TO_MANY:
                case ClassMetadata::MANY_TO_ONE:
                    $options = [
                        'multiple' => $metadata->isCollectionValuedAssociation($property),
                    ];
                    if (isset($mapping['targetDocument'])) {
                        $options['class'] = $mapping['targetDocument'];
                    }

                    return new TypeGuess(DocumentType::class, $options, Guess::HIGH_CONFIDENCE);

                case 'child':
                    $options = [
                        'attr' => ['readonly' => 'readonly'],
                    ];

                    return new TypeGuess(PathType::class, $options, Guess::LOW_CONFIDENCE);

                case 'children':
                    $options = [
                        'attr' => ['readonly' => 'readonly'],
                        'entry_type' => PathType::class,
                    ];

                    return new TypeGuess(CollectionType::class, $options, Guess::LOW_CONFIDENCE);

                default:
                    return;
            }
        }

        $mapping = $metadata->getFieldMapping($property);

        if (!empty($mapping['assoc'])) {
            if (isset($this->typeGuess['assoc'])) {
                return new TypeGuess(
                    key($this->typeGuess['assoc']),
                    current($this->typeGuess['assoc']),
                    Guess::MEDIUM_CONFIDENCE
                );
            }

            return;
        }

        $options = [];
        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                $type = CheckboxType::class;

                break;
            case 'binary':
                // the file type only works on documents like the File document,
                // not directly on properties with raw binary data.
                return;
            case 'node':
                // editing the phpcr node has no meaning
                return;
            case 'date':
                $type = DateTimeType::class;

                break;
            case 'double':
                $type = NumberType::class;

                break;
            case 'long':
            case 'integer':
                $type = IntegerType::class;

                break;
            case 'string':
                if ($metadata->isIdentifier($property)
                    || $metadata->isUuid($property)
                ) {
                    $options['attr'] = ['readonly' => 'readonly'];
                }
                $type = TextType::class;

                break;
            case 'nodename':
                $type = TextType::class;

                break;
            case 'locale':
                $locales = $documentManager->getLocaleChooserStrategy();
                $type = ChoiceType::class;
                $options['choices'] = array_combine($locales->getDefaultLocalesOrder(), $locales->getDefaultLocalesOrder());

                break;
            case 'versionname':
            case 'versioncreated':
            default:
                $options['attr'] = ['readonly' => 'readonly'];
                $options['required'] = false;
                $type = TextType::class;

                break;
        }

        if (!empty($mapping['multivalue'])) {
            $options['entry_type'] = $type;
            $type = CollectionType::class;
        }

        if (!empty($mapping['translated'])) {
            $options['attr'] = ['class' => 'translated'];
        }

        return new TypeGuess($type, $options, Guess::HIGH_CONFIDENCE);
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessMinLength($class, $property)
    {
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
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
    }

    private function getMetadata($class): ?array
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $manager = $this->registry->getManagerForClass($class);
        if ($manager) {
            return $this->cache[$class] = [$manager->getClassMetadata($class), $manager];
        }

        return $this->cache[$class] = null;
    }
}
