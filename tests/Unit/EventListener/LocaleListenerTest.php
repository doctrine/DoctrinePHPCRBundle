<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\EventListener;

use Doctrine\Bundle\PHPCRBundle\EventListener\LocaleListener;
use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListenerTest extends TestCase
{
    /**
     * @var LocaleChooserInterface&MockObject
     */
    private LocaleChooserInterface $chooser;

    /**
     * @var RequestEvent&MockObject
     */
    private RequestEvent $responseEvent;

    /**
     * @var Request&MockObject
     */
    private Request $request;

    private array $allowedLocales;

    protected function setUp(): void
    {
        $this->chooser = $this->createMock(LocaleChooserInterface::class);
        $this->responseEvent = $this->createMock(RequestEvent::class);
        $this->request = $this->createMock(Request::class);
        $this->allowedLocales = ['fr', 'en', 'de'];
    }

    public function testOnKernelRequestWithFallbackHardcoded(): void
    {
        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales,
            LocaleListener::FALLBACK_HARDCODED
        );

        $this->responseEvent->expects($this->exactly(4))
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('it', 'fr'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('fr'));

        $localeListener->onKernelRequest($this->responseEvent);
        $localeListener->onKernelRequest($this->responseEvent);
    }

    public function testOnKernelRequestWithDefaultFallback(): void
    {
        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales
        );

        $this->responseEvent->expects($this->exactly(2))
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request->expects($this->once())
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('en'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en'));

        $this->request->expects($this->once())
            ->method('getLanguages')
            ->willReturn(['it', 'fr_FR', 'fr_CA', 'en_GB']);

        $this->chooser->expects($this->once())
            ->method('setFallbackLocales')
            ->with('en', ['fr', 'en'], false);

        $localeListener->onKernelRequest($this->responseEvent);
    }

    public function testOnKernelRequestWithFallbackReplace(): void
    {
        $localeListener = new LocaleListener(
            $this->chooser,
            $this->allowedLocales,
            LocaleListener::FALLBACK_REPLACE
        );

        $this->responseEvent->expects($this->exactly(2))
            ->method('getRequest')
            ->willReturn($this->request);

        $this->request->expects($this->once())
            ->method('getLocale')
            ->will($this->onConsecutiveCalls('en'));

        $this->chooser->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('en'));

        $this->request->expects($this->once())
            ->method('getLanguages')
            ->willReturn(['it', 'fr_FR', 'fr_CA', 'en_GB']);

        $this->chooser->expects($this->once())
            ->method('setFallbackLocales')
            ->with('en', ['fr', 'en'], true);

        $localeListener->onKernelRequest($this->responseEvent);
    }
}
