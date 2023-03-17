<?php

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A listener to tell the locale chooser the request locale.
 *
 * This listener is invoked on every sub-request, keeping the locale up to date.
 *
 * If a fallback type other than hardcoded is specified, the LocaleChooserInterface
 * is also updated with the fallback locales to use based on the Accept-Language
 * header.
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * Append locales not in request header but in configured fallback.
     */
    public const FALLBACK_MERGE = 'merge';

    /**
     * Only use locales from request.
     */
    public const FALLBACK_REPLACE = 'replace';

    /**
     * Do not look into request.
     */
    public const FALLBACK_HARDCODED = 'hardcoded';

    /**
     * @var LocaleChooserInterface
     */
    private $chooser;

    /**
     * Whether to update the locale chooser to update the allowed languages.
     *
     * @var string|null one of the FALLBACK_ constants or empty
     */
    private $fallback = null;

    /**
     * List of allowed locales to set on the LocaleChooserInterface.
     *
     * @var array
     */
    private $allowedLocales;

    /**
     * @param array  $allowedLocales list of locales that are allowed
     * @param string $fallback       one of the FALLBACK_* constants
     */
    public function __construct(LocaleChooserInterface $chooser, array $allowedLocales, $fallback = self::FALLBACK_MERGE)
    {
        $this->chooser = $chooser;
        $this->allowedLocales = $allowedLocales;
        switch ($fallback) {
            case self::FALLBACK_MERGE:
            case self::FALLBACK_REPLACE:
            case self::FALLBACK_HARDCODED:
                $this->fallback = $fallback;

                break;
            default:
                $this->fallback = self::FALLBACK_MERGE;

                break;
        }
    }

    /**
     * Decides which locale will be used.
     *
     * @return mixed string|null a locale or null if no valid locale is found
     */
    protected function determineLocale(RequestEvent $event)
    {
        $locale = $event->getRequest()->getLocale();

        return \in_array($locale, $this->allowedLocales) ?
            $locale :
            null;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$locale = $this->determineLocale($event)) {
            return;
        }

        $this->chooser->setLocale($locale);

        if (self::FALLBACK_HARDCODED === $this->fallback) {
            return;
        }

        // expand language list to include base locales
        // copy-pasted from Request::getPreferredLanguage
        $preferredLanguages = $request->getLanguages();
        $extendedPreferredLanguages = [];
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (false !== $position = strpos($language, '_')) {
                $superLanguage = substr($language, 0, $position);
                if (!\in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }
        $order = array_intersect($this->allowedLocales, $extendedPreferredLanguages);
        $this->chooser->setFallbackLocales($locale, $order, self::FALLBACK_REPLACE === $this->fallback);
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 1]],
        ];
    }
}
