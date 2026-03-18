<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(\Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand::class, \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand::class)
        ->args([
            '%doctrine_phpcr.jackrabbit_jar%',
            '%doctrine_phpcr.workspace_dir%',
        ])
        ->tag('console.command');

    $services->alias('Doctrine\Bundle\PHPCRBundle\OptionalCommand\JackrabbitCommand', \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand::class)
        ->deprecate('doctrine/phpcr-bundle', '3.1.0', 'Service %alias_id% was misnamed, use the correct class name instead '.\Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand::class);
};
