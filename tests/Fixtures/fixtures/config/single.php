<?php

use Jackalope\Factory;

$container->loadFromExtension('doctrine_phpcr', [
    'session' => [
        'backend' => [
            'type' => 'jackrabbit',
            'url' => 'http://localhost:8080/server/',
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
        ],
        'workspace' => 'default',
        'username' => 'admin',
        'password' => 'admin',
        'options' => [
            'jackalope.fetch_depth' => 1,
        ],
    ],

    'odm' => [
        'configuration_id' => null,
        'auto_mapping' => true,
        'auto_generate_proxy_classes' => true,
        'proxy-dir' => '/doctrine/PHPCRProxies',
        'proxy_namespace' => 'PHPCRProxies',
        'mappings' => [
            'test' => [
                'mapping' => true,
                'type' => null,
                'dir' => null,
                'prefix' => null,
                'is-bundle' => null,
            ],
        ],

        'metadata_cache_driver' => [
            'type' => 'array',
        ],

        'locales' => [
            'en' => ['de', 'fr'],
            'de' => ['en', 'fr'],
            'fr' => ['en', 'de'],
        ],
        'locale_fallback' => 'hardcoded',
        'default_locale' => 'fr',
    ],
    'jackrabbit_jar' => '/path/to/jackrabbit.jar',
    'dump_max_line_length' => 20,
    'manager_registry_service_id' => 'my_phpcr_registry',
]);
