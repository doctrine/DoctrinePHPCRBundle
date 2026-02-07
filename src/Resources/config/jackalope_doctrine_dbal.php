<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('doctrine_phpcr.jackalope_doctrine_dbal.schema', \Jackalope\Transport\DoctrineDBAL\RepositorySchema::class)
        ->args([
            [],
            service('doctrine_phpcr.jackalope_doctrine_dbal.default_connection'),
        ])
        ->lazy(true);

    $services->set('doctrine_phpcr.jackalope_doctrine_dbal.schema_listener', \Doctrine\Bundle\PHPCRBundle\EventListener\JackalopeDoctrineDbalSchemaListener::class)
        ->args([
            service('doctrine_phpcr.jackalope_doctrine_dbal.schema'),
        ]);
};
