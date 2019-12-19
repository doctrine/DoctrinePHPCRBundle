<?php

namespace Doctrine\Bundle\PHPCRBundle\CacheWarmer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Tools\Helper\UniqueNodeTypeHelper;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Hook the verification of uniquely mapped node types into the cache
 * warming process, thereby providing a useful indication to the
 * developer that something is wrong.
 */
class UniqueNodeTypeCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * This cache warmer is optional as it is just for error
     * checking and reporting back to the user.
     *
     * @return true
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $helper = new UniqueNodeTypeHelper();

        foreach ($this->registry->getManagers() as $documentManager) {
            $helper->checkNodeTypeMappings($documentManager);
        }
    }
}
