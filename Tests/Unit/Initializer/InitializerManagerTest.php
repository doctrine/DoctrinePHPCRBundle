<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class InitializerManagerTest extends TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    public function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->initializerManager = new InitializerManager($this->registry);

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
    }

    public function provideInitialize()
    {
        return array(
            array(
                array(
                    array('initializer1', 0),
                    array('initializer2', 0),
                    array('initializer3', 0),
                ),
                array('initializer1', 'initializer2', 'initializer3'),
            ),
            array(
                array(
                    array('initializer1', null),
                    array('initializer2', null),
                    array('initializer3', null),
                ),
                array('initializer1', 'initializer2', 'initializer3'),
            ),
            array(
                array(
                    array('initializer3', 0),
                    array('initializer1', 0),
                    array('initializer2', 0),
                ),
                array('initializer3', 'initializer1', 'initializer2'),
            ),
            array(
                array(
                    array('initializer3', 100),
                    array('initializer1', -100),
                    array('initializer2', 0),
                ),
                array('initializer3', 'initializer2', 'initializer1'),
            ),
        );
    }

    /**
     * @dataProvider provideInitialize
     */
    public function testInitialize($initializers, $expectedOrder)
    {
        foreach ($initializers as $initializerConfig) {
            list($initializerVar, $priority) = $initializerConfig;

            $initializer = $this->$initializerVar;

            $initializer->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($initializerVar));

            $initializer->expects($this->once())
                ->method('init')
                ->with($this->registry);

            if (null !== $priority) {
                $this->initializerManager->addInitializer($initializer, $priority);
            } else {
                $this->initializerManager->addInitializer($initializer);
            }
        }

        $log = array();
        $this->initializerManager->setLoggingClosure(function ($message) use (&$log) {
            $log[] = $message;
        });

        $this->initializerManager->initialize();

        $this->assertCount(count($initializers), $log);

        // check expected order against the log
        foreach ($expectedOrder as $i => $initializerVar) {
            $this->assertContains($initializerVar, $log[$i]);
        }
    }

    public function testNoLogging()
    {
        $this->initializer1->expects($this->never())
            ->method('getName');
        $this->initializer1->expects($this->once())
            ->method('init');

        $this->initializerManager->addInitializer($this->initializer1);
        $this->initializerManager->initialize();
    }
}
