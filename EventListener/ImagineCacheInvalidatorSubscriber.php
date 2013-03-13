<?php

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\Common\Util\Debug;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
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
    /**
     * @var CacheManager
     */
    private $manager;

    /**
     * Used to get the request from to remove cache
     * @var Container
     */
    private $container;

    /**
     * Filter names to invalidate
     * @var array
     */
    private $filters;

    /**
     * @param CacheManager $manager   the imagine cache manager
     * @param Container    $container to get the request from. Need to inject
     *      this as otherwise we have a scope problem
     * @param array        $filter    list of filter names to invalidate
     */
    public function __construct(CacheManager $manager, Container $container, $filters)
    {
        $this->manager = $manager;
        $this->container = $container;
        $this->filters = $filters;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate',
            'preRemove',
        );
    }

    /**
     * Invalidate cache after a document was updated.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->invalidateCache($args);
    }

    /**
     * Invalidate the cache when removing an image. Do this before the flush to
     * still have access to the parent of the document.
     *
     * @param LifecycleEventArgs $args
     */
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
            foreach ($this->filters as $filter) {
                $path = $this->manager->resolve($this->container->get('request'), $document->getId(), 'image_upload_thumbnail')->getTargetUrl();
                // TODO: this might not be needed https://github.com/liip/LiipImagineBundle/issues/162
                if (false !== strpos($path, $filter)) {
                    $path = substr($path, strpos($path, $filter) + strlen($filter));
                }
                $this->manager->remove($path, $filter);
            }
        }
    }

}
