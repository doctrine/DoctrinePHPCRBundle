<?php

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\Common\Util\Debug;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Data\Loader\DoctrinePHPCRLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A listener to tell the locale chooser the request locale.
 *
 * This listener is invoked on every sub-request, keeping the locale up to date.
 */
class ImageCacheInvalidatorListener
{

    protected $phpcrLoader;

    public function __construct(DoctrinePHPCRLoader $phpcrLoader)
    {

    }
    public function postUpdate(LifecycleEventArgs $args)
    {

    }

}
