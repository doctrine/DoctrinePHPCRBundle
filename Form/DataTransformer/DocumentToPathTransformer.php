<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ODM\PHPCR\DocumentManager;

class DocumentToPathTransformer implements DataTransformerInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Transform a document into a path
     *
     * @param object|null $document
     *
     * @return string|null the path to the document or null if $document is null
     */
    public function transform($document)
    {
        if (null === $document) {
            return null;
        }

        $path = $this->dm->getUnitOfWork()->getDocumentId($document);

        return $path;
    }

    /**
     * Transform a path to its corresponding PHPCR document
     *
     * @param string $path phpcr path
     *
     * @return \PHPCR\NodeInterface|null returns the document or null if $path is empty
     *
     * @throws \PHPCR\ItemNotFoundException if document for a non-empty $path is not found
     */
    public function reverseTransform($path)
    {
        if (!$path) {
            return null;
        }

        return $this->dm->find(null, $path);
    }
}

