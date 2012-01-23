<?php

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * A listener to tell the locale chooser the request locale.
 *
 * This listener is invoked on every sub-request, keeping the locale up to date.
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * The locale chooser to update on each request
     *
     * @param LocaleChooser $chooser
     */
    public function __construct(LocaleChooser $chooser)
    {
        $this->chooser = $chooser;
    }

    /**
     * Handling the request event
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $this->chooser->setLocale($request->getLocale());
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }

}