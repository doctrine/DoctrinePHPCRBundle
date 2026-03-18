<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(\Doctrine\Bundle\PHPCRBundle\OptionalCommand\InitDoctrineDbalCommand::class, \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand::class)
        ->tag('console.command');
};
