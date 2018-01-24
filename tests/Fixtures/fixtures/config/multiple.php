<?php

$container->loadFromExtension('doctrine_phpcr', [
    'session' => [
        'sessions' => [
            'default' => [
                'backend' => [
                    'type' => 'jackrabbit',
                    'url' => 'http://a',
                ],
                'workspace' => 'default',
                'username' => 'admin',
                'password' => 'admin',
            ],
            'website' => [
                'backend' => [
                    'type' => 'jackrabbit',
                    'url' => 'http://b',
                    'factory' => null,
                ],
                'workspace' => 'website',
                'username' => 'root',
                'password' => 'root',
                'admin_username' => 'admin',
                'admin_password' => 'admin',
            ],
        ],
    ],
    'odm' => [
        'auto_generate_proxy_classes' => true,
        'document_managers' => [
            'default' => [
                'session' => 'default',
                'mappings' => [
                    'SandboxMainBundle' => null,
                ],
            ],
            'website' => [
                'session' => 'website',
                'configuration_id' => 'sandbox_magnolia.odm_configuration',
                'mappings' => [
                    'SandboxMagnoliaBundle' => null,
                ],
            ],
        ],
    ],
]);
