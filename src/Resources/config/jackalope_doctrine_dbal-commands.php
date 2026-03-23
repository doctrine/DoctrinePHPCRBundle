<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(\Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand::class, \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand::class)
        ->tag('console.command');

    $services->alias('Doctrine\Bundle\PHPCRBundle\OptionalCommand\InitDoctrineDbalCommand', \Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand::class)
        ->deprecate('doctrine/phpcr-bundle', '3.1.0', 'Service %alias_id% was misnamed, use the correct class name instead '.\Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand::class);
};
