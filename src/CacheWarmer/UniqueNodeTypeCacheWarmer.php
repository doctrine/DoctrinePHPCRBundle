<?php

namespace Doctrine\Bundle\PHPCRBundle\CacheWarmer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\ODM\PHPCR\Tools\Helper\UniqueNodeTypeHelper;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Hook the verification of uniquely mapped node types into the cache
 * warming process, thereby providing a useful indication to the
 * developer that something is wrong.
 */
final class UniqueNodeTypeCacheWarmer implements CacheWarmerInterface
{
    private ManagerRegistryInterface $registry;

    public function __construct(ManagerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * This cache warmer is optional as it is just for error
     * checking and reporting back to the user.
     */
    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp($cacheDir): void
    {
        $helper = new UniqueNodeTypeHelper();

        foreach ($this->registry->getManagers() as $documentManager) {
            $helper->checkNodeTypeMappings($documentManager);
        }
    }
}
