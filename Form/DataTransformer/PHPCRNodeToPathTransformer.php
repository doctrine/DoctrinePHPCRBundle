<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use PHPCR\SessionInterface;
use PHPCR\NodeInterface;
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
     * Transform a node into a path
     *
     * @param PHPCR\NodeInterface
     * @throws UnexpectedTypeException if given value is not a PHPCR\NodeInterface
     * @return string|null
     */
    public function transform($node)
    {
        if (null === $node) {
            return null;
        }

        if (!$node instanceof NodeInterface) {
            throw new UnexpectedTypeException($node, 'PHPCR\NodeInterface');
        }
        return $node->getPath();
    }

    /**
     * Transform a path/uuid to its corresponding PHPCR node
     *
     * @param string $path/uuid 
     * @throws PHPCR\ItemNotFoundException if node not found
     * @return PHPCR\NodeInterface|null
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $node = $this->session->getNodeByIdentifier($id);

        return $node;
    }
}
