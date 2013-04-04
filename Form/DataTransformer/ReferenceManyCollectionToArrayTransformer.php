<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Joiz\CmsBundle\Document\Show;
use Doctrine\ODM\PHPCR\ReferenceManyCollection;
use Doctrine\ODM\PHPCR\DocumentManager;

class ReferenceManyCollectionToArrayTransformer implements DataTransformerInterface
{

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager $dm
     */
    protected $dm;

    /**
     * @var string $referencedClass
     */
    protected $referencedClass;

    /**
     * @var bool $useUuidAsArrayKey
     */
    protected $useUuidAsArrayKey;


    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $dm
     * @param $referencedClass
     * @param $useUuidAsArrayKey
     */
    function __construct(DocumentManager $dm, $referencedClass, $useUuidAsArrayKey)
    {
        $this->dm = $dm;
        $this->referencedClass = $referencedClass;
        $this->useUuidAsArrayKey = $useUuidAsArrayKey;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        $arr = array();

        foreach ($collection as $item) {
            $arr[] = $this->useUuidAsArrayKey ? $item->getNode()->getPropertyValue('jcr:uuid') : $item->getPath();
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