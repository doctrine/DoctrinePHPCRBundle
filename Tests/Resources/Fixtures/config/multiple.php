<?php

$container->loadFromExtension('doctrine_phpcr', array(
    'session' => array(
        'sessions' => array(
            'default' => array(
                'backend' => array(
                    'type' => 'jackrabbit',
                    'url' => 'http://a',
                ),
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ),
            'website' => array(
                'backend' => array(
                    'type' => 'jackrabbit',
                    'url' => 'http://b',
                    'factory' => null,
                ),
                'workspace' => 'website',
                'username' => 'root',
                'password' => 'root',
            ),
        ),
    ),
    'odm' => array(
        'auto_generate_proxy_classes' => true,
        'document_managers' => array(
            'default' => array(
                'session' => 'default',
                'mappings' => array(
                    'SandboxMainBundle' => null,
                ),
            ),
            'website' => array(
                'session' => 'website',
                'configuration_id' => 'sandbox_magnolia.odm_configuration',
                'mappings' => array(
                    'SandboxMagnoliaBundle' => null,
                ),
            ),
        ),
    ),
));
