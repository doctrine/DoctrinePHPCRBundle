<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ODM\PHPCR\ReferenceManyCollection;
use Doctrine\ODM\PHPCR\DocumentManager;

class ReferenceManyCollectionToArrayTransformer implements DataTransformerInterface
{
    const KEY_PATH = 'path';
    const KEY_UUID = 'uuid';

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager $dm
     */
    protected $dm;

    /**
     * @var string $referencedClass
     */
    protected $referencedClass;

    /**
     * @var string $key
     */
    protected $key;

    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $dm
     * @param $referencedClass
     * @param string $key
     * @throws \InvalidArgumentException
     */
    function __construct(DocumentManager $dm, $referencedClass, $key = self::KEY_UUID)
    {
        $this->dm = $dm;
        $this->referencedClass = $referencedClass;

        if (!($key === self::KEY_UUID || $key === self::KEY_PATH)) {
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
        $arr = array();

        foreach ($collection as $item) {
            $arr[] = ($this->key === self::KEY_UUID) ? $item->getNode()->getIdentifier() : $item->getPath();
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