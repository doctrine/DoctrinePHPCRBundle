<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use PHPCR\ItemNotFoundException;
use PHPCR\NodeInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PHPCRNodeToUuidTransformer implements DataTransformerInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Transform a node into a uuid.
     *
     * @param NodeInterface|null $node
     *
     * @return string|null the uuid to the node or null if $node is null
     *
     * @throws UnexpectedTypeException if given value is not a NodeInterface
     */
    public function transform($node)
    {
        if (null === $node) {
            return;
        }

        if (!$node instanceof NodeInterface) {
            throw new UnexpectedTypeException($node, NodeInterface::class);
        }

        return $node->getIdentifier();
    }

    /**
     * Transform a uuid to its corresponding PHPCR node.
     *
     * @param string $id uuid
     *
     * @return NodeInterface|null returns the node or null if the $id is empty
     *
     * @throws ItemNotFoundException if node for a non-empty $id is not found
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        return $this->session->getNodeByIdentifier($id);
    }
}
