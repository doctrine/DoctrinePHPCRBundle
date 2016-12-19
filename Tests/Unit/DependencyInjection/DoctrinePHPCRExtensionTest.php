<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePHPCRExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new DoctrinePHPCRExtension(),
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.root_dir', null);
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.bundles', array());
        $this->container->setParameter('kernel.debug', false);
    }

    public function testLoad()
    {
        $this->load();
    }

    public function testJackrabbitSession()
    {
        $this->load(array(
            'session' => array(
                'backend' => array(
                    'url' => 'http://localhost',
                ),
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ),
        ));

        /** @var $repositoryFactory DefinitionDecorator */
        $repositoryFactory = $this->container->getDefinition('doctrine_phpcr.jackalope.repository.default');
        $parameters = $repositoryFactory->getArgument(0);
        $this->assertEquals(array(
            'jackalope.jackrabbit_uri',
            'jackalope.check_login_on_server',
        ), array_keys($parameters));

        $this->assertEquals('doctrine_phpcr.jackalope.repository.factory.jackrabbit', $repositoryFactory->getParent());

        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.default_session'));
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.jackalope.repository.default'));
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.admin.default_session'));
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.admin.jackalope.repository.default'));
    }

    public function testCustomManagerRegistryService()
    {
        $this->container->setDefinition('my_phpcr_registry', new Definition('\stdClass'));

        $this->load(array(
            'session' => array(
                'backend' => array(
                    'url' => 'http://localhost',
                ),
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ),
            'manager_registry_service_id' => 'my_phpcr_registry',
        ));

        $managerRegistry = $this->container->getAlias('doctrine_phpcr');
        $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Alias', $managerRegistry);
        $this->assertEquals('my_phpcr_registry', $managerRegistry);
    }

    public function testJackrabbitSessions()
    {
        $this->load(array(
            'session' => array(
                'default_session' => 'bar',
                'sessions' => array(
                    'foo' => array(
                        'backend' => array(
                            'url' => 'http://foo',
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                    ),
                    'bar' => array(
                        'backend' => array(
                            'url' => 'http://bar',
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                    ),
                ),
            ),
        ));

        $this->assertCount(2, $this->container->getParameter('doctrine_phpcr.sessions'));

        foreach ($this->container->getParameter('doctrine_phpcr.sessions') as $id) {
            $this->container->getDefinition($id);
        }
    }

    public function testDoctrineDbalSession()
    {
        $this->load(array(
            'session' => array(
                'backend' => array(
                    'type' => 'doctrinedbal',
                    'logging' => true,
                    'profiling' => true,
                    'factory' => 'my_factory',
                    'parameters' => array(
                        'jackalope.check_login_on_server' => false,
                        'jackalope.disable_stream_wrapper' => false,
                        'jackalope.auto_lastmodified' => true,
                    ),
                ),
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
                'options' => array(
                    'jackalope.fetch_depth' => 2,
                ),
            ),
        ));

        /** @var $repositoryFactory DefinitionDecorator */
        $repositoryFactory = $this->container->getDefinition('doctrine_phpcr.jackalope.repository.default');
        $parameters = $repositoryFactory->getArgument(0);
        $this->assertInternalType('array', $parameters);
        $this->assertEquals(array(
            'jackalope.doctrine_dbal_connection',
            'jackalope.check_login_on_server',
            'jackalope.disable_stream_wrapper',
            'jackalope.auto_lastmodified',
            'jackalope.factory',
            'jackalope.logger',
        ), array_keys($parameters));

        $this->assertEquals('my_factory', (string) $parameters['jackalope.factory']);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $parameters['jackalope.factory']);

        $this->assertEquals('doctrine_phpcr.jackalope.repository.factory.doctrinedbal', $repositoryFactory->getParent());

        /** @var $session Definition */
        $session = $this->container->getDefinition('doctrine_phpcr.default_session');
        $calls = $session->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertEquals(array('setSessionOption', array('jackalope.fetch_depth', 2)), current($calls));
    }

    public function provideLocaleChooser()
    {
        return array(
            array(
                array(
                    'odm' => array(
                        'locales' => array('fr' => array('de', 'en')),
                    ),
                ),
                'doctrine_phpcr.odm.locale_chooser',
            ),
            array(
                array(
                    'odm' => array(
                        'locales' => array('fr' => array('de', 'en')),
                        'locale_chooser' => 'foobar',
                    ),
                ),
                'foobar',
            ),
            array(
                array(
                    'odm' => array(
                        'locale_chooser' => 'foobar',
                    ),
                ),
                'foobar',
            ),
        );
    }

    /**
     * @dataProvider provideLocaleChooser
     */
    public function testLocales($odmConfig, $expectedChooser)
    {
        $this->load(array_merge(array(
            'session' => array(
                'backend' => array(
                    'type' => 'doctrinedbal',
                ),
                'workspace' => 'default',
            ),
        ), $odmConfig));

        $managerDef = $this->container->getDefinition('doctrine_phpcr.odm.document_manager.abstract');
        $calls = $managerDef->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertEquals(array('setLocaleChooserStrategy', array(new Reference($expectedChooser))), current($calls));
    }
}
