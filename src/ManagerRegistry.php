<?php

namespace Doctrine\Bundle\PHPCRBundle;

use Doctrine\ODM\PHPCR\PHPCRException;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry as BaseManagerRegistry;

/**
 * Symfony aware manager registry.
 *
 * @internal this class is intended to be used as service, but not for code reuse
 */
class ManagerRegistry extends BaseManagerRegistry
{
    public function __construct(
        ContainerInterface $container,
        array $connections,
        array $entityManagers,
        $defaultConnectionName,
        $defaultEntityManagerName,
        $proxyInterfaceName
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
     * @return string
     *
     * @throws PHPCRException
     */
    public function getAliasNamespace($alias)
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getDocumentNamespace($alias);
            } catch (PHPCRException $e) {
            }
        }

        throw PHPCRException::unknownDocumentNamespace($alias);
    }

    /**
     * Get the admin connection associated to the connection.
     *
     * @param string $name
     *
     * @return object
     */
    public function getAdminConnection($name = null)
    {
        if (null === $name) {
            $name = $this->getDefaultConnectionName();
        }

        $serviceName = sprintf('doctrine_phpcr.admin.%s_session', $name);

        $connections = $this->getConnectionNames();
        if (!isset($connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->getName(), $name));
        }

        return $this->getService($serviceName);
    }
}
