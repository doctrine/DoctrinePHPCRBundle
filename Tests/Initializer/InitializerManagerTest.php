<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Initializer;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;

class InitializerManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->session = $this->getMock('PHPCR\SessionInterface');
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\PHPCRBundle\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->session));

        $this->initializerManager = new InitializerManager($this->registry);

        $this->initializer1 = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface'
        )->setMockClassName('TestInitializerOne')->getMock();
        $this->initializer2 = $this->getMockBuilder(
            'Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface'
        )->setMockClassName('TestInitializerTwo')->getMock();
    }

    public function provideInitialize()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideInitialize
     */
    public function testInitialize($withLogging)
    {
        foreach (array(
            $this->initializer1, $this->initializer2
        ) as $initializer) {
            $initializer->expects($this->once())
                ->method('init')
                ->with($this->session);
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
            $this->assertCount(2, $log);
            $this->assertContains('TestInitializerOne', $log[0]);
            $this->assertContains('TestInitializerTwo', $log[1]);

        }
    }
}
