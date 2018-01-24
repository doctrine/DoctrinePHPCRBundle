<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Unit\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Configuration;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension()
    {
        return new DoctrinePHPCRExtension();
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }

    /**
     * @dataProvider configurations
     */
    public function testSupports($expectedConfiguration, array $files)
    {
        $formats = array_map(function ($path) {
            return __DIR__.'/../../Fixtures/fixtures/'.$path;
        }, $files);

        foreach ($formats as $format) {
            $this->assertProcessedConfigurationEquals($expectedConfiguration, array($format));
        }
    }

    public function configurations()
    {
        $singleConfiguration = array(
            'session' => array(
                'default_session' => 'default',
                'sessions' => array(
                    'default' => array(
                        'backend' => array(
                            'type' => 'jackrabbit',
                            'logging' => true,
                            'profiling' => true,
                            'factory' => null,
                            'parameters' => array(
                                'jackalope.factory' => 'Jackalope\Factory',
                                'jackalope.check_login_on_server' => false,
                                'jackalope.disable_stream_wrapper' => false,
                                'jackalope.auto_lastmodified' => true,
                                'jackalope.default_header' => 'X-ID: %serverid%',
                                'jackalope.jackrabbit_expect' => true,
                            ),
                            'curl_options' => array(),
                            'url' => 'http://localhost:8080/server/',
                            'backtrace' => false,
                            ),
                            'workspace' => 'default',
                            'username' => 'admin',
                            'password' => 'admin',
                            'options' => array(
                                'jackalope.fetch_depth' => 1,
                            ),
                            'admin_username' => null,
                            'admin_password' => null,
                        ),
                    ),
            ),
            'odm' => array(
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '/doctrine/PHPCRProxies',
                'proxy_namespace' => 'PHPCRProxies',
                'locales' => array(
                    'en' => array('de', 'fr'),
                    'de' => array('en', 'fr'),
                    'fr' => array('en', 'de'),
                ),
                'locale_fallback' => 'hardcoded',
                'default_locale' => 'fr',
                'default_document_manager' => 'default',
                'document_managers' => array(
                    'default' => array(
                        'configuration_id' => null,
                        'auto_mapping' => true,
                        'mappings' => array(
                            'test' => array(
                                'mapping' => true,
                                'type' => null,
                                'dir' => null,
                                'alias' => null,
                                'prefix' => null,
                                'is_bundle' => true,
                            ),
                        ),
                        'metadata_cache_driver' => array(
                            'type' => 'array',
                            'host' => null,
                            'port' => null,
                            'instance_class' => null,
                            'class' => null,
                            'id' => null,
                            'namespace' => null,
                        ),
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                    ),
                ),
                'namespaces' => array(
                    'translation' => array(
                        'alias' => 'phpcr_locale',
                    ),
                ),
                'locale_chooser' => null,
            ),
            'jackrabbit_jar' => '/path/to/jackrabbit.jar',
            'dump_max_line_length' => 20,
            'manager_registry_service_id' => 'my_phpcr_registry',
        );
        $multipleConfiguration = array(
            'session' => array(
                'sessions' => array(
                    'default' => array(
                        'backend' => array(
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'factory' => null,
                            'parameters' => array(
                            ),
                            'curl_options' => array(),
                            'url' => 'http://a',
                            'backtrace' => false,
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => array(
                        ),
                        'admin_username' => null,
                        'admin_password' => null,
                    ),
                    'website' => array(
                        'backend' => array(
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'parameters' => array(
                            ),
                            'curl_options' => array(),
                            'url' => 'http://b',
                            'backtrace' => false,
                            'factory' => null,
                        ),
                        'workspace' => 'website',
                        'username' => 'root',
                        'password' => 'root',
                        'options' => array(
                        ),
                        'admin_username' => 'admin',
                        'admin_password' => 'admin',
                    ),
                ),
            ),
            'odm' => array(
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '%kernel.cache_dir%/doctrine/PHPCRProxies',
                'proxy_namespace' => 'PHPCRProxies',
                'locales' => array(
                ),
                'locale_fallback' => 'merge',
                'document_managers' => array(
                    'default' => array(
                        'auto_mapping' => false,
                        'mappings' => array(
                            'SandboxMainBundle' => array(
                                'mapping' => true,
                            ),
                        ),
                        'metadata_cache_driver' => array(
                            'type' => 'array',
                            'namespace' => null,
                        ),
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                        'session' => 'default',
                    ),
                    'website' => array(
                        'auto_mapping' => false,
                        'mappings' => array(
                            'SandboxMagnoliaBundle' => array(
                                'mapping' => true,
                            ),
                        ),
                        'metadata_cache_driver' => array(
                            'type' => 'array',
                            'namespace' => null,
                        ),
                        'class_metadata_factory_name' => ClassMetadataFactory::class,
                        'default_repository_class' => DocumentRepository::class,
                        'repository_factory' => null,
                        'session' => 'website',
                        'configuration_id' => 'sandbox_magnolia.odm_configuration',
                    ),
                ),
                'namespaces' => array(
                    'translation' => array(
                        'alias' => 'phpcr_locale',
                    ),
                ),
                'locale_chooser' => null,
            ),
            'dump_max_line_length' => 120,
            'manager_registry_service_id' => null,
        );
        $bc = array(
            'session' => array(
                'default_session' => 'default',
                'sessions' => array(
                    'default' => array(
                        'backend' => array(
                            'type' => 'doctrinedbal',
                            'logging' => false,
                            'profiling' => false,
                            'factory' => null,
                            'backtrace' => false,
                            'parameters' => array(
                                'jackalope.factory' => 'Jackalope\Factory',
                                'jackalope.check_login_on_server' => true,
                                'jackalope.disable_stream_wrapper' => true,
                                'jackalope.disable_transactions' => true,
                            ),
                            'curl_options' => array(),
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => array(),
                        'admin_username' => null,
                        'admin_password' => null,
                    ),
                ),
            ),
            'dump_max_line_length' => 120,
            'manager_registry_service_id' => null,
        );

        return array(
            array(
                $singleConfiguration,
                array(
                    'config/single.yml',
                    'config/single.xml',
                    'config/single.php',
                ),
            ),
            array(
                $multipleConfiguration,
                array(
                    'config/multiple.yml',
                    'config/multiple.xml',
                    'config/multiple.php',
                ),
            ),
            array(
                $bc,
                array('config/bc.php'),
            ),
        );
    }
}
