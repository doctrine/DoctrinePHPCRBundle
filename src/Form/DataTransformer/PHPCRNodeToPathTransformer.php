<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use PHPCR\NodeInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PHPCRNodeToPathTransformer implements DataTransformerInterface
{
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Transform a node into a path.
     *
     * @param \PHPCR\NodeInterface|null $node
     *
     * @return string|null the path to the node or null if $node is null
     *
     * @throws UnexpectedTypeException if given value is not a \PHPCR\NodeInterface
     */
    public function transform($node)
    {
        if (null === $node) {
            return;
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
     * @return \PHPCR\NodeInterface|null returns the node or null if $path is empty
     *
     * @throws \PHPCR\ItemNotFoundException if node for a non-empty $path is not found
     */
    public function reverseTransform($path)
    {
        if (!$path) {
            return;
        }

        return $this->session->getNode($path);
    }
}
