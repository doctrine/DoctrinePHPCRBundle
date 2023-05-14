<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DocumentToPathTransformer implements DataTransformerInterface
{
    private DocumentManagerInterface $dm;

    public function __construct(DocumentManagerInterface $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Transform a document into a path.
     *
     * @param object $document
     */
    public function transform($document): ?string
    {
        if (null === $document) {
            return null;
        }

        return $this->dm->getUnitOfWork()->getDocumentId($document);
    }

    /**
     * Transform a path to its corresponding PHPCR-ODM document.
     *
     * @param string $path phpcr path
     *
     * @return object|null returns the document or null if $path is empty
     */
    public function reverseTransform($path): ?object
    {
        if (!$path) {
            return null;
        }

        $document = $this->dm->find(null, $path);

        if (!$document) {
            throw new TransformationFailedException(sprintf('Could not transform path "%s" to document. Path not found.', $path));
        }

        return $document;
    }
}
