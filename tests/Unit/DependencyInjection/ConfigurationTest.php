<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Configuration;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Jackalope\Factory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension(): ExtensionInterface
    {
        return new DoctrinePHPCRExtension();
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * @dataProvider configurations
     */
    public function testSupports(array $expectedConfiguration, array $files): void
    {
        $formats = array_map(function ($path) {
            return __DIR__.'/../../Fixtures/fixtures/'.$path;
        }, $files);

        foreach ($formats as $format) {
            $this->assertProcessedConfigurationEquals($expectedConfiguration, [$format]);
        }
    }

    public function configurations(): array
    {
        $singleConfiguration = [
            'session' => [
                'default_session' => 'default',
                'sessions' => [
                    'default' => [
                        'backend' => [
                            'type' => 'jackrabbit',
                            'logging' => true,
                            'profiling' => true,
                            'factory' => null,
                            'parameters' => [
                                'jackalope.factory' => Factory::class,
                                'jackalope.check_login_on_server' => false,
                                'jackalope.disable_stream_wrapper' => false,
                                'jackalope.auto_lastmodified' => true,
                                'jackalope.default_header' => 'X-ID: %serverid%',
                                'jackalope.jackrabbit_expect' => true,
                            ],
                            'curl_options' => [],
                            'url' => 'http://localhost:8080/server/',
                            'backtrace' => false,
                        ],
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => [
                            'jackalope.fetch_depth' => 1,
                        ],
                        'admin_username' => null,
                        'admin_password' => null,
                    ],
                ],
            ],
            'odm' => [
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '/doctrine/PHPCRProxies',
                'proxy_namespace' => 'PHPCRProxies',
                'locales' => [
                    'en' => ['de', 'fr'],
                    'de' => ['en', 'fr'],
                    'fr' => ['en', 'de'],
                ],
                'locale_fallback' => 'hardcoded',
                'default_locale' => 'fr',
                'default_document_manager' => 'default',
                'document_managers' => [
                    'default' => [
                        'configuration_id' => null,
                        'auto_mapping' => true,
                        'mappings' => [
                            'test' => [
                                'mapping' => true,
                                'type' => null,
                                'dir' => null,
                                'prefix' => null,
                                'is_bundle' => true,
                            ],
                        ],
                        'metadata_cache_driver' => [
                            'type' => 'array',
                        ],
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                    ],
                ],
                'namespaces' => [
                    'translation' => [
                        'alias' => 'phpcr_locale',
                    ],
                ],
                'locale_chooser' => null,
            ],
            'jackrabbit_jar' => '/path/to/jackrabbit.jar',
            'dump_max_line_length' => 20,
            'manager_registry_service_id' => 'my_phpcr_registry',
        ];
        $multipleConfiguration = [
            'session' => [
                'sessions' => [
                    'default' => [
                        'backend' => [
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'factory' => null,
                            'parameters' => [
                            ],
                            'curl_options' => [],
                            'url' => 'http://a',
                            'backtrace' => false,
                        ],
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => [
                        ],
                        'admin_username' => null,
                        'admin_password' => null,
                    ],
                    'website' => [
                        'backend' => [
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'parameters' => [
                            ],
                            'curl_options' => [],
                            'url' => 'http://b',
                            'backtrace' => false,
                            'factory' => null,
                        ],
                        'workspace' => 'website',
                        'username' => 'root',
                        'password' => 'root',
                        'options' => [
                        ],
                        'admin_username' => 'admin',
                        'admin_password' => 'admin',
                    ],
                ],
            ],
            'odm' => [
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '%kernel.cache_dir%/doctrine/PHPCRProxies',
                'proxy_namespace' => 'PHPCRProxies',
                'locales' => [
                ],
                'locale_fallback' => 'merge',
                'document_managers' => [
                    'default' => [
                        'auto_mapping' => false,
                        'mappings' => [
                            'SandboxMainBundle' => [
                                'mapping' => true,
                            ],
                        ],
                        'metadata_cache_driver' => [
                            'type' => 'array',
                        ],
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                        'session' => 'default',
                    ],
                    'website' => [
                        'auto_mapping' => false,
                        'mappings' => [
                            'SandboxMagnoliaBundle' => [
                                'mapping' => true,
                            ],
                        ],
                        'metadata_cache_driver' => [
                            'type' => 'array',
                        ],
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                        'session' => 'website',
                        'configuration_id' => 'sandbox_magnolia.odm_configuration',
                    ],
                ],
                'namespaces' => [
                    'translation' => [
                        'alias' => 'phpcr_locale',
                    ],
                ],
                'locale_chooser' => null,
            ],
            'dump_max_line_length' => 120,
            'manager_registry_service_id' => null,
        ];

        return [
            'single-configuration' => [
                $singleConfiguration,
                [
                    'config/single.yml',
                    'config/single.xml',
                    'config/single.php',
                ],
            ],
            'multiple-configuration' => [
                $multipleConfiguration,
                [
                    'config/multiple.yml',
                    'config/multiple.xml',
                    'config/multiple.php',
                ],
            ],
        ];
    }
}
