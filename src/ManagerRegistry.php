<?php

namespace Doctrine\Bundle\PHPCRBundle;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\PHPCRException;
use PHPCR\SessionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry as BaseManagerRegistry;

/**
 * Symfony aware manager registry.
 */
final class ManagerRegistry extends BaseManagerRegistry implements ManagerRegistryInterface
{
    /**
     * @param string[] $connections
     * @param string[] $entityManagers
     */
    public function __construct(
        ContainerInterface $container,
        array $connections,
        array $entityManagers,
        string $defaultConnectionName,
        string $defaultEntityManagerName,
        string $proxyInterfaceName
    ) {
        $this->container = $container;

        parent::__construct(
            'PHPCR',
            $connections,
            $entityManagers,
            $defaultConnectionName,
            $defaultEntityManagerName,
            $proxyInterfaceName
        );
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * @param string $alias
     *
     * @throws PHPCRException
     */
    public function getAliasNamespace($alias): string
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getDocumentNamespace($alias);
            } catch (PHPCRException $e) {
            }
        }

        throw PHPCRException::unknownDocumentNamespace($alias);
    }

    public function getManager($name = null): DocumentManagerInterface
    {
        $dm = parent::getManager($name);
        \assert($dm instanceof DocumentManagerInterface);

        return $dm;
    }

    public function resetManager($name = null): DocumentManagerInterface
    {
        $dm = parent::resetManager($name);
        \assert($dm instanceof DocumentManagerInterface);

        return $dm;
    }

    public function getManagerForClass($class = null): ?DocumentManagerInterface
    {
        $dm = parent::getManagerForClass($class);
        \assert(null === $dm || $dm instanceof DocumentManagerInterface);

        return $dm;
    }

    public function getConnection($name = null): SessionInterface
    {
        $conn = parent::getConnection($name);
        \assert($conn instanceof SessionInterface);

        return $conn;
    }

    /**
     * Get the admin connection associated to the connection.
     */
    public function getAdminConnection(?string $name = null): SessionInterface
    {
        if (null === $name) {
            $name = $this->getDefaultConnectionName();
        }

        $serviceName = sprintf('doctrine_phpcr.admin.%s_session', $name);

        $connections = $this->getConnectionNames();
        if (!isset($connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->getName(), $name));
        }

        $connection = $this->getService($serviceName);
        \assert($connection instanceof SessionInterface);

        return $connection;
    }
}
