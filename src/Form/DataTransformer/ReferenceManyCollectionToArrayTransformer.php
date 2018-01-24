<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\ReferenceManyCollection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @deprecated This is only used by the deprecated PHPCRODMReferenceCollectionType.
 *      Will be removed in 1.2.
 */
class ReferenceManyCollectionToArrayTransformer implements DataTransformerInterface
{
    const KEY_PATH = 'path';

    const KEY_UUID = 'uuid';

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $referencedClass;

    /**
     * @var string
     */
    protected $key;

    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $dm
     * @param $referencedClass
     * @param string $key
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(DocumentManager $dm, $referencedClass, $key = self::KEY_UUID)
    {
        @trigger_error('This is deprecated in favor of phpcr_document. If you think this is an error, please contact us and explain. We were not able to figure out what this type is good for.', E_USER_DEPRECATED);

        $this->dm = $dm;
        $this->referencedClass = $referencedClass;

        if (!(self::KEY_UUID === $key || self::KEY_PATH === $key)) {
            throw new \InvalidArgumentException(sprintf(
                'Key must be either KEY_UUID or KEY_PATH. Received "%s"',
                $key
            ));
        }

        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        $arr = [];

        foreach ($collection as $item) {
            $arr[] = (self::KEY_UUID === $this->key) ? $item->getNode()->getIdentifier() : $item->getPath();
        }

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($array)
    {
        return new ReferenceManyCollection($this->dm, $array, $this->referencedClass);
    }
}
