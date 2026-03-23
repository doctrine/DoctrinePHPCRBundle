<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceQueryCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceQueryCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\MigratorMigrateCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\MigratorMigrateCommand::class)
        ->args([service('service_container')])
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeDumpCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeDumpCommand::class)
        ->args([
            service('doctrine_phpcr.console_dumper'),
            '%doctrine_phpcr.dump_max_line_length%',
        ])
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeMoveCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeMoveCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeRemoveCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeRemoveCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodesUpdateCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodesUpdateCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeTouchCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeTouchCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeTypeListCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeTypeListCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\NodeTypeRegisterCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\NodeTypeRegisterCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\PhpcrShellCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\PhpcrShellCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\RepositoryInitCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\RepositoryInitCommand::class)
        ->args([service('doctrine_phpcr.initializer_manager')])
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceCreateCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceCreateCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceDeleteCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceDeleteCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceExportCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceExportCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceImportCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceImportCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspaceListCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspaceListCommand::class)
        ->tag('console.command');

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\WorkspacePurgeCommand::class, \Doctrine\Bundle\PHPCRBundle\Command\WorkspacePurgeCommand::class)
        ->tag('console.command');
};
