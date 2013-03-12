<?php

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\Common\Util\Debug;
use Doctrine\ODM\PHPCR\Document\Image;
use Doctrine\ODM\PHPCR\Document\File;
use Doctrine\ODM\PHPCR\Document\Resource;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\Loader\DoctrinePHPCRLoader;

/**
 * A listener to invalidate the imagine cache when Image documents are modified
 */
class ImagineCacheInvalidatorSubscriber implements EventSubscriber
{

    public function __construct(CacheManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate',
            'preRemove', // when removing, do before the flush to still get parents
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    /**
     * Check if this could mean an image document was modified (check resource,
     * file and image)
     *
     * @param LifecycleEventArgs $args
     */
    private function invalidateCache(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof Resource) {
            $document = $document->getParent();
        }
        if ($document instanceof File) {
            $document = $document->getParent();
        }
        if ($document instanceof Image) {
            // TODO: this does not work, what do we need to pass to manager->remove?
            // TODO: can we invalidate all caches? otherwise inject filter name(s)? by config
            $this->manager->remove($document->getId(), 'image_upload_thumbnail');
        }
    }

}
