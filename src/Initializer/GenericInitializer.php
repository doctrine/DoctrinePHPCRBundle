<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use PHPCR\SessionInterface;
use PHPCR\Util\NodeHelper;

/**
 * Initializer to create node types and create nt:unstructured nodes at the specified paths.
 *
 * The node types will be created first, in case extending classes want to
 * create nodes of those types. To not create any node types, pass null
 * for the $cnd.
 *
 * The nodes will be created as nt:unstructured nodes. To not create any
 * nodes, pass an empty array.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class GenericInitializer implements InitializerInterface, SessionAwareInitializerInterface
{
    private string $name;
    private ?string $cnd;

    /**
     * List of base paths to create.
     *
     * @var string[]
     */
    private array $basePaths;

    /**
     * Name of the session in which this initializer should run.
     *
     * If this is null, the default session is used.
     */
    private ?string $sessionName = null;

    /**
     * @param string      $name      name to identify this initializer instance
     * @param array       $basePaths a list of base paths to create if not existing
     * @param string|null $cnd       node type and namespace definitions in cnd
     *                               format, pass null to not create any node types
     */
    public function __construct(string $name, array $basePaths, ?string $cnd = null)
    {
        $this->cnd = $cnd;
        $this->basePaths = $basePaths;
        $this->name = $name;
    }

    public function init(ManagerRegistryInterface $registry): void
    {
        $session = $registry->getConnection($this->sessionName);

        if ($this->cnd) {
            $this->registerCnd($session, $this->cnd);
        }
        if (\count($this->basePaths)) {
            $this->createBasePaths($session, $this->basePaths);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSessionName(string $sessionName): void
    {
        $this->sessionName = $sessionName;
    }

    protected function registerCnd(SessionInterface $session, string $cnd): void
    {
        $session->getWorkspace()->getNodeTypeManager()->registerNodeTypesCnd($cnd, true);
    }

    protected function createBasePaths(SessionInterface $session, array $basePaths): void
    {
        foreach ($basePaths as $path) {
            NodeHelper::createPath($session, $path);
        }

        $session->save();
    }
}
