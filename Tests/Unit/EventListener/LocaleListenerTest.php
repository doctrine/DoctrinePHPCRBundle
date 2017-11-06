<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\EventListener\LocaleListenerTest;

use Doctrine\Bundle\PHPCRBundle\EventListener\LocaleListener;
use PHPUnit\Framework\TestCase;

class LocaleListenerTest extends TestCase
{
    private $chooser;
    private $responseEvent;
    private $request;
    private $allowedLocales;

    private function setUpTestOnKernelRequest()
    {
        $this->chooser = $this
            ->getMockBuilder(
                'Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseEvent = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this
            ->getMockBuilder(
                'Symfony\Component\HttpFoundation\Request'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->allowedLocales = array('fr', 'en', 'de');
    }

    public function testOnKernelRequestWithFallbackHardcoded()
    {
        $this->setUpTestOnKernelRequest();

        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales,
            LocaleListener::FALLBACK_HARDCODED
        );

        $this->responseEvent->expects($this->exactly(4))
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->request->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('it', 'fr'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('fr'));

        $localeListener->onKernelRequest($this->responseEvent);
        $localeListener->onKernelRequest($this->responseEvent);
    }

    public function testOnKernelRequestWithDefaultFallback()
    {
        $this->setUpTestOnKernelRequest();

        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales,
            null
        );

        $this->responseEvent->expects($this->exactly(2))
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->request->expects($this->once())
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('en'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en'));

        $this->request->expects($this->once())
            ->method('getLanguages')
            ->will($this->returnValue(array('it', 'fr_FR', 'fr_CA', 'en_GB')));

        $this->chooser->expects($this->once())
            ->method('setFallbackLocales')
            ->with('en', array('fr', 'en'), false);

        $localeListener->onKernelRequest($this->responseEvent);
    }

    public function testOnKernelRequestWithFallbackReplace()
    {
        $this->setUpTestOnKernelRequest();

        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales,
            LocaleListener::FALLBACK_REPLACE
        );

        $this->responseEvent->expects($this->exactly(2))
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->request->expects($this->once())
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('en'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en'));

        $this->request->expects($this->once())
            ->method('getLanguages')
            ->will($this->returnValue(array('it', 'fr_FR', 'fr_CA', 'en_GB')));

        $this->chooser->expects($this->once())
            ->method('setFallbackLocales')
            ->with('en', array('fr', 'en'), true);

        $localeListener->onKernelRequest($this->responseEvent);
    }
}
