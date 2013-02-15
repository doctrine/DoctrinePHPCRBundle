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
     * @return string
     */
    public function transform($node)
    {
        if (!$node instanceof NodeInterface) {
            throw new UnexpectedTypeException($node, 'PHPCR\NodeInterface');
        }
        return $node->getIdentifier();
    }

    /**
     * Transform a path to its corresponding PHPCR node
     *
     * @param string $path 
     * @throws PHPCR\ItemNotFoundException if node not found
     * @return PHPCR\NodeInterface
     */
    public function reverseTransform($path)
    {
        $node = $this->session->getNodeByIdentifier($path);

        return $node;
    }
}
