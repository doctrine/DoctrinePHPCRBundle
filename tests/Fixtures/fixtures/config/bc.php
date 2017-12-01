<?php

$container->loadFromExtension('doctrine_phpcr', array(
    'session' => array(
        'backend' => array(
            'type' => 'doctrinedbal',
            'parameters' => array(
                'jackalope.factory' => 'Jackalope\Factory',
            ),
            'check_login_on_server' => true,
            'disable_stream_wrapper' => true,
            'disable_transactions' => true,
        ),
        'workspace' => 'default',
        'username' => 'admin',
        'password' => 'admin',
    ),
));
