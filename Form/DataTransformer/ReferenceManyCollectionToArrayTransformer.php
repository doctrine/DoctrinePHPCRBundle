<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
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
     * @param \Doctrine\ODM\PHPCR\DocumentManager $dm
     * @param $referencedClass
     */
    function __construct(DocumentManager $dm, $referencedClass)
    {
        $this->dm = $dm;
        $this->referencedClass = $referencedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        $arr = array();

        foreach ($collection as $item) {
            $arr[] = $item->getPath();
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