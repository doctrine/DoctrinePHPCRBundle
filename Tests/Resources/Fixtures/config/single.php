<?php

$container->loadFromExtension('doctrine_phpcr', array(
    'session' => array(
        'backend' => array(
            'type' => 'jackrabbit',
            'url' => 'http://localhost:8080/server/',
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
        ),
        'workspace' => 'default',
        'username' => 'admin',
        'password' => 'admin',
        'options' => array(
            'jackalope.fetch_depth' => 1,
        ),
    ),

    'odm' => array(
        'configuration_id' => null,
        'auto_mapping' => true,
        'auto_generate_proxy_classes' => true,
        'proxy-dir' => '/doctrine/PHPCRProxies',
        'proxy_namespace' => 'PHPCRProxies',
        'mappings' => array(
            'test' => array(
                'mapping' => true,
                'type' => null,
                'dir' => null,
                'alias' => null,
                'prefix' => null,
                'is-bundle' => null,
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

        'locales' => array(
            'en' => array('de', 'fr'),
            'de' => array('en', 'fr'),
            'fr' => array('en', 'de'),
        ),
        'locale_fallback' => 'hardcoded',
    ),
    'jackrabbit_jar' => '/path/to/jackrabbit.jar',
    'dump_max_line_length' => 20,
));
