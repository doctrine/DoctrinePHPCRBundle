<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\EventListener;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * A listener to tell the locale chooser the request locale.
 *
 * This listener is invoked on every sub-request, keeping the locale up to date.
 *
 * If a fallback type other than hardcoded is specified, the LocaleChooser is
 * also updated with the fallback locales to use based on the Accept-Language
 * header.
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * Append locales not in request header but in configured fallback.
     */
    const FALLBACK_MERGE = 'merge';

    /**
     * Only use locales from request.
     */
    const FALLBACK_REPLACE = 'replace';

    /**
     * Do not look into request.
     */
    const FALLBACK_HARDCODED = 'hardcoded';

    /**
     * @var LocaleChooser
     */
    private $chooser;

    /**
     * Whether to update the locale chooser to update the allowed languages.
     *
     * @var string|null one of the FALLBACK_ constants or empty
     */
    private $fallback = null;

    /**
     * List of allowed locales to set on the LocaleChooser.
     *
     * @var array
     */
    private $allowedLocales;

    /**
     * The locale chooser to update on each request.
     *
     * @param LocaleChooser $chooser        the locale chooser to update
     * @param array         $allowedLocales list of locales that are allowed
     * @param string        $fallback       one of the FALLBACK_* constants
     */
    public function __construct(LocaleChooser $chooser, array $allowedLocales, $fallback = self::FALLBACK_MERGE)
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
     * @param GetResponseEvent $event used to get the request
     *
     * @return mixed string|null a locale or null if no valid locale is found
     */
    protected function determineLocale(GetResponseEvent $event)
    {
        $locale = $event->getRequest()->getLocale();

        return in_array($locale, $this->allowedLocales) ?
            $locale :
            null;
    }

    /**
     * Handling the request event.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$locale = $this->determineLocale($event)) {
            return;
        }

        $this->chooser->setLocale($locale);

        if (self::FALLBACK_HARDCODED == $this->fallback) {
            return;
        }

        // expand language list to include base locales
        // copy-pasted from Request::getPreferredLanguage
        $preferredLanguages = $request->getLanguages();
        $extendedPreferredLanguages = array();
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (false !== $position = strpos($language, '_')) {
                $superLanguage = substr($language, 0, $position);
                if (!in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }
        $order = array_intersect($this->allowedLocales, $extendedPreferredLanguages);
        $this->chooser->setFallbackLocales($locale, $order, self::FALLBACK_REPLACE == $this->fallback);
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }
}
