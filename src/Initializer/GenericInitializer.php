<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use PHPCR\SessionInterface;
use PHPCR\Util\NodeHelper;

/**
 * In most cases, this initializer should be usable as is by bundles.
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
    /**
     * Name for this initializer.
     *
     * @var string
     */
    protected $name;

    /**
     * The cnd definition.
     *
     * @var string
     */
    protected $cnd;

    /**
     * List of base paths to create.
     *
     * @var array
     */
    protected $basePaths;

    /**
     * Name of the session in which this initializer should run.
     *
     * @var string
     */
    protected $sessionName;

    /**
     * @param array       $basePaths a list of base paths to create if not existing
     * @param string|null $cnd       node type and namespace definitions in cnd
     *                               format, pass null to not create any node types
     */
    public function __construct($name, array $basePaths, $cnd = null)
    {
        $this->cnd = $cnd;
        $this->basePaths = $basePaths;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function init(ManagerRegistry $registry)
    {
        $session = $registry->getConnection($this->sessionName);

        if ($this->cnd) {
            $this->registerCnd($session, $this->cnd);
        }
        if (count($this->basePaths)) {
            $this->createBasePaths($session, $this->basePaths);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSessionName($sessionName)
    {
        $this->sessionName = $sessionName;
    }

    protected function registerCnd(SessionInterface $session, $cnd)
    {
        $session->getWorkspace()->getNodeTypeManager()->registerNodeTypesCnd($cnd, true);
    }

    protected function createBasePaths(SessionInterface $session, array $basePaths)
    {
        foreach ($basePaths as $path) {
            NodeHelper::createPath($session, $path);
        }

        $session->save();
    }
}
