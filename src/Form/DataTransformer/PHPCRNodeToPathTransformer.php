<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use PHPCR\ItemNotFoundException;
use PHPCR\NodeInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PHPCRNodeToPathTransformer implements DataTransformerInterface
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Transform a node into a path.
     *
     * @param NodeInterface|null $node
     *
     * @return string|null the path to the node or null if $node is null
     *
     * @throws UnexpectedTypeException if given value is not a NodeInterface
     */
    public function transform($node): ?string
    {
        if (null === $node) {
            return null;
        }

        if (!$node instanceof NodeInterface) {
            throw new UnexpectedTypeException($node, NodeInterface::class);
        }

        return $node->getPath();
    }

    /**
     * Transform a path to its corresponding PHPCR node.
     *
     * @param string $path phpcr path
     *
     * @return NodeInterface|null returns the node or null if $path is empty
     *
     * @throws ItemNotFoundException if node for a non-empty $path is not found
     */
    public function reverseTransform($path): ?NodeInterface
    {
        if (!$path) {
            return null;
        }

        return $this->session->getNode($path);
    }
}
