<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use PHPCR\SessionInterface;
use PHPCR\NodeInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PHPCRNodeToUuidTransformer extends PHPCRNodeToPathTransformer
{
    /**
     * Transform a node into a path
     *
     * @param PHPCR\NodeInterface
     * @throws UnexpectedTypeException if given value is not a PHPCR\NodeInterface
     * @return string
     */
    public function transform($node)
    {
        if (null === $node) {
            return null;
        }

        if (!$node instanceof NodeInterface) {
            throw new UnexpectedTypeException($node, 'PHPCR\NodeInterface');
        }
        return $node->getIdentifier();
    }
}
