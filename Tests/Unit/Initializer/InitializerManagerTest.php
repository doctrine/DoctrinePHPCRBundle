<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Bundle\PHPCRBundle\Initializer\PhpcrInitializerInterface;
use Doctrine\Bundle\PHPCRBundle\Initializer\PhpcrOdmInitializerInterface;

class InitializerManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->registry = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\ManagerRegistry'
        )->disableOriginalConstructor()->getMock();

        $this->initializerManager = new InitializerManager($this->registry);

        $this->initializer1 = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface'
        )->setMockClassName('TestInitializerOne')->getMock();;

        $this->initializer2 = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface'
        )->setMockClassName('TestInitializerTwo')->getMock();;
    }

    public function provideInitialize()
    {
        return array(
            array(array('initializer1', 'initializer2'), true),
            array(array('initializer1', 'initializer2'), false),
        );
    }

    /**
     * @dataProvider provideInitialize
     */
    public function testInitialize($initializerVars, $withLogging)
    {
        foreach ($initializerVars as $initializerVar) {
            $initializer = $this->$initializerVar;

            if ($withLogging) {
                $initializer->expects($this->once())
                    ->method('getName')
                    ->will($this->returnValue($initializerVar));
            }

            $initializer->expects($this->once())
                ->method('init')
                ->with($this->registry);

            $this->initializerManager->addInitializer($initializer);
        }

        if ($withLogging) {
            $log = array();
            $this->initializerManager->setLoggingClosure(function ($message) use (&$log) {
                $log[] = $message;
            });
        }

        $this->initializerManager->initialize();

        if ($withLogging) {
            $this->assertCount(count($initializerVars), $log);
            foreach ($initializerVars as $i => $initializerVar) {
                $this->assertContains($initializerVar, $log[$i]);
            }
        }
    }
}
