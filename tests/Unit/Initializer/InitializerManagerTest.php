<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitializerManagerTest extends TestCase
{
    /**
     * @var ManagerRegistryInterface&MockObject
     */
    private ManagerRegistryInterface $registry;

    /**
     * @var InitializerInterface&MockObject
     */
    private InitializerInterface $initializer1;
    /**
     * @var InitializerInterface&MockObject
     */
    private InitializerInterface $initializer2;
    /**
     * @var InitializerInterface&MockObject
     */
    private InitializerInterface $initializer3;

    private InitializerManager $initializerManager;

    public function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistryInterface::class);

        $this->initializer1 = $this
            ->getMockBuilder(InitializerInterface::class)
            ->setMockClassName('TestInitializerOne')
            ->getMock();
        $this->initializer2 = $this
            ->getMockBuilder(InitializerInterface::class)
            ->setMockClassName('TestInitializerTwo')
            ->getMock();
        $this->initializer3 = $this
            ->getMockBuilder(InitializerInterface::class)
            ->setMockClassName('TestInitializerTwo')
            ->getMock();

        $this->initializerManager = new InitializerManager($this->registry);
    }

    public function provideInitialize(): array
    {
        return [
            [
                [
                    ['initializer1', 0],
                    ['initializer2', 0],
                    ['initializer3', 0],
                ],
                ['initializer1', 'initializer2', 'initializer3'],
            ],
            [
                [
                    ['initializer1', null],
                    ['initializer2', null],
                    ['initializer3', null],
                ],
                ['initializer1', 'initializer2', 'initializer3'],
            ],
            [
                [
                    ['initializer3', 0],
                    ['initializer1', 0],
                    ['initializer2', 0],
                ],
                ['initializer3', 'initializer1', 'initializer2'],
            ],
            [
                [
                    ['initializer3', 100],
                    ['initializer1', -100],
                    ['initializer2', 0],
                ],
                ['initializer3', 'initializer2', 'initializer1'],
            ],
        ];
    }

    /**
     * @dataProvider provideInitialize
     */
    public function testInitialize(array $initializers, array $expectedOrder): void
    {
        foreach ($initializers as $initializerConfig) {
            [$initializerVar, $priority] = $initializerConfig;

            $initializer = $this->$initializerVar;

            $initializer->expects($this->once())
                ->method('getName')
                ->willReturn($initializerVar);

            $initializer->expects($this->once())
                ->method('init')
                ->with($this->registry);

            if (null !== $priority) {
                $this->initializerManager->addInitializer($initializer, $priority);
            } else {
                $this->initializerManager->addInitializer($initializer);
            }
        }

        $log = [];
        $this->initializerManager->setLoggingClosure(function ($message) use (&$log) {
            $log[] = $message;
        });

        $this->initializerManager->initialize();

        $this->assertCount(\count($initializers), $log);

        // check expected order against the log
        foreach ($expectedOrder as $i => $initializerVar) {
            $this->assertStringContainsString($initializerVar, $log[$i]);
        }
    }

    public function testNoLogging(): void
    {
        $this->initializer1->expects($this->never())
            ->method('getName');
        $this->initializer1->expects($this->once())
            ->method('init');

        $this->initializerManager->addInitializer($this->initializer1);
        $this->initializerManager->initialize();
    }
}
