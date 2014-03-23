<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Configuration;
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
            return __DIR__.'/../../Resources/Fixtures/'.$path;
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
                            'parameters' => array(
                                'jackalope.factory' => 'Jackalope\Factory',
                                'jackalope.check_login_on_server' => false,
                                'jackalope.disable_stream_wrapper' => false,
                                'jackalope.auto_lastmodified' => true,
                                'jackalope.default_header' => 'X-ID: %serverid%',
                                'jackalope.jackrabbit_expect' => true,
                            ),
                            'url' => 'http://localhost:8080/server/',
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => array(
                            'jackalope.fetch_depth' => 1,
                        ),
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
                        ),
                        'class_metadata_factory_name' => 'Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory',
                        'default_repository_class' => 'Doctrine\ODM\PHPCR\DocumentRepository',
                        'repository_factory' => null,
                    ),
                ),
            ),
            'jackrabbit_jar' => '/path/to/jackrabbit.jar',
            'dump_max_line_length' => 20,
        );
        $multipleConfiguration = array(
            'session' => array(
                'sessions' => array(
                    'default' => array(
                        'backend' => array(
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'parameters' => array(
                            ),
                            'url' => 'http://a',
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => array(
                        ),
                    ),
                    'website' => array(
                        'backend' => array(
                            'type' => 'jackrabbit',
                            'logging' => false,
                            'profiling' => false,
                            'parameters' => array(
                            ),
                            'url' => 'http://b',
                        ),
                        'workspace' => 'website',
                        'username' => 'root',
                        'password' => 'root',
                        'options' => array(
                        ),
                    ),
                ),
            ),
            'odm' => array(
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '%kernel.cache_dir%/doctrine/PHPCRProxies',
                'proxy_namespace' => 'PHPCRProxies',
                'locales' => array(
                ),
                'locale_fallback' => 'hardcoded',
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
                        ),
                        'class_metadata_factory_name' => 'Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory',
                        'default_repository_class' => 'Doctrine\ODM\PHPCR\DocumentRepository',
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
                        ),
                        'class_metadata_factory_name' => 'Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory',
                        'default_repository_class' => 'Doctrine\ODM\PHPCR\DocumentRepository',
                        'repository_factory' => null,
                        'session' => 'website',
                        'configuration_id' => 'sandbox_magnolia.odm_configuration',
                    ),
                ),
            ),
            'dump_max_line_length' => 120,
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
                            'parameters' => array(
                                'jackalope.factory' => 'Jackalope\Factory',
                                'jackalope.check_login_on_server' => true,
                                'jackalope.disable_stream_wrapper' => true,
                                'jackalope.disable_transactions' => true,
                            ),
                        ),
                        'workspace' => 'default',
                        'username' => 'admin',
                        'password' => 'admin',
                        'options' => array(),
                    ),
                ),
            ),
            'dump_max_line_length' => 120,
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
            )
        );
    }
}
