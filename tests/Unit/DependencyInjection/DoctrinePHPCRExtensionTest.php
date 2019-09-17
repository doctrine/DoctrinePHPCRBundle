<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePHPCRExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new DoctrinePHPCRExtension(),
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->setParameter('kernel.name', 'app');
        $this->setParameter('kernel.root_dir', null);
        $this->setParameter('kernel.project_dir', null);
        $this->setParameter('kernel.container_class', null);
        $this->setParameter('kernel.environment', 'test');
        $this->setParameter('kernel.bundles', []);
        $this->setParameter('kernel.debug', false);
    }

    /**
     * Check that the extension loads without error.
     */
    public function testLoad()
    {
        $this->assertNull($this->load());
    }

    public function testJackrabbitSession()
    {
        $this->load([
            'session' => [
                'backend' => [
                    'url' => 'http://localhost',
                ],
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ],
        ]);

        /** @var $repositoryFactory DefinitionDecorator */
        $repositoryFactory = $this->container->getDefinition('doctrine_phpcr.jackalope.repository.default');
        $parameters = $repositoryFactory->getArgument(0);
        $this->assertEquals([
            'jackalope.jackrabbit_uri',
            'jackalope.check_login_on_server',
        ], array_keys($parameters));

        $this->assertEquals('doctrine_phpcr.jackalope.repository.factory.jackrabbit', $repositoryFactory->getParent());

        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.default_session'));
        $this->assertTrue($this->container->getDefinition('doctrine_phpcr.default_session')->isPublic());
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.jackalope.repository.default'));
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.admin.default_session'));
        $this->assertTrue($this->container->hasDefinition('doctrine_phpcr.admin.jackalope.repository.default'));
    }

    public function testCustomManagerRegistryService()
    {
        $this->registerService('my_phpcr_registry', \stdClass::class);

        $this->load([
            'session' => [
                'backend' => [
                    'url' => 'http://localhost',
                ],
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ],
            'manager_registry_service_id' => 'my_phpcr_registry',
        ]);

        $this->assertContainerBuilderHasAlias('doctrine_phpcr', 'my_phpcr_registry');

        $managerRegistry = $this->container->getAlias('doctrine_phpcr');
        $this->assertInstanceOf(Alias::class, $managerRegistry);
        $this->assertTrue($managerRegistry->isPublic());
    }

    public function testJackrabbitSessions()
    {
        $this->load([
            'session' => [
                'default_session' => 'bar',
                'sessions' => [
                    'foo' => [
                        'backend' => [
                            'url' => 'http://foo',
                        ],
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                    ],
                    'bar' => [
                        'backend' => [
                            'url' => 'http://bar',
                        ],
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                    ],
                ],
            ],
        ]);

        $sessions = $this->container->getParameter('doctrine_phpcr.sessions');

        $this->assertCount(2, $sessions);

        foreach ($sessions as $id) {
            $this->assertContainerBuilderHasService($id);
        }
    }

    public function testDoctrineDbalSession()
    {
        $this->load([
            'session' => [
                'backend' => [
                    'type' => 'doctrinedbal',
                    'logging' => true,
                    'profiling' => true,
                    'factory' => 'my_factory',
                    'parameters' => [
                        'jackalope.check_login_on_server' => false,
                        'jackalope.disable_stream_wrapper' => false,
                        'jackalope.auto_lastmodified' => true,
                    ],
                ],
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
                'options' => [
                    'jackalope.fetch_depth' => 2,
                ],
            ],
        ]);

        /** @var $repositoryFactory DefinitionDecorator */
        $repositoryFactory = $this->container->getDefinition('doctrine_phpcr.jackalope.repository.default');
        $parameters = $repositoryFactory->getArgument(0);

        $this->assertInternalType('array', $parameters);
        $this->assertEquals([
            'jackalope.doctrine_dbal_connection',
            'jackalope.check_login_on_server',
            'jackalope.disable_stream_wrapper',
            'jackalope.auto_lastmodified',
            'jackalope.factory',
            'jackalope.logger',
        ], array_keys($parameters));

        $this->assertEquals('my_factory', (string) $parameters['jackalope.factory']);
        $this->assertInstanceOf(Reference::class, $parameters['jackalope.factory']);

        $this->assertEquals('doctrine_phpcr.jackalope.repository.factory.doctrinedbal', $repositoryFactory->getParent());

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'doctrine_phpcr.default_session',
            'setSessionOption',
            ['jackalope.fetch_depth', 2]
        );
    }

    public function provideLocaleChooser()
    {
        return [
            [
                [
                    'odm' => [
                        'locales' => ['fr' => ['de', 'en']],
                    ],
                ],
                'doctrine_phpcr.odm.locale_chooser',
            ],
            [
                [
                    'odm' => [
                        'locales' => ['fr' => ['de', 'en']],
                        'locale_chooser' => 'foobar',
                    ],
                ],
                'foobar',
            ],
            [
                [
                    'odm' => [
                        'locale_chooser' => 'foobar',
                    ],
                ],
                'foobar',
            ],
        ];
    }

    /**
     * @dataProvider provideLocaleChooser
     */
    public function testLocales($odmConfig, $expectedChooser)
    {
        $this->load(array_merge([
            'session' => [
                'backend' => [
                    'type' => 'doctrinedbal',
                ],
                'workspace' => 'default',
            ],
        ], $odmConfig));

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'doctrine_phpcr.odm.document_manager.abstract',
            'setLocaleChooserStrategy',
            [new Reference($expectedChooser)]
        );

        $this->assertTrue($this->container->getDefinition('doctrine_phpcr.odm.document_manager.abstract')->isPublic());
    }
}
