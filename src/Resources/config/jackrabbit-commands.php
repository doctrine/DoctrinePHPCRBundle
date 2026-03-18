<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(\Doctrine\Bundle\PHPCRBundle\OptionalCommand\JackrabbitCommand::class, \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand::class)
        ->args([
            '%doctrine_phpcr.jackrabbit_jar%',
            '%doctrine_phpcr.workspace_dir%',
        ])
        ->tag('console.command');
};
