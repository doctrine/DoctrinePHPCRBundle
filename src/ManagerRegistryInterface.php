<?php

namespace Doctrine\Bundle\PHPCRBundle;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\Persistence\ManagerRegistry as BaseManagerRegistry;
use PHPCR\SessionInterface;

/**
 * Symfony aware manager registry for PHPCR-ODM.
 *
 * @method DocumentManagerInterface[] getManagers()
 */
interface ManagerRegistryInterface extends BaseManagerRegistry
{
    public function getManager($name = null): DocumentManagerInterface;

    public function resetManager($name = null): DocumentManagerInterface;

    public function getManagerForClass($class = null): ?DocumentManagerInterface;

    public function getConnection($name = null): SessionInterface;

    /**
     * Get the admin connection associated to the connection.
     */
    public function getAdminConnection(?string $name = null): SessionInterface;
}
