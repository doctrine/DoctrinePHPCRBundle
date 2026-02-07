<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('doctrine_phpcr.session.event_manager', \Symfony\Bridge\Doctrine\ContainerAwareEventManager::class)
        ->private()
        ->abstract()
        ->args([service('service_container')]);

    $services->set('doctrine_phpcr.logger.chain', \Jackalope\Transport\Logging\LoggerChain::class)
        ->private()
        ->abstract()
        ->call('addLogger', [service('doctrine_phpcr.logger')]);

    $services->set('doctrine_phpcr.logger.profiling', \Jackalope\Transport\Logging\DebugStack::class)
        ->private()
        ->abstract();

    $services->set('doctrine_phpcr.logger.stop_watch', \Doctrine\Bundle\PHPCRBundle\DataCollector\StopWatchLogger::class)
        ->private()
        ->abstract()
        ->args([service('debug.stopwatch')->nullOnInvalid()]);

    $services->set('doctrine_phpcr.logger', \Jackalope\Transport\Logging\Psr3Logger::class)
        ->private()
        ->args([service('logger')->nullOnInvalid()])
        ->tag('monolog.logger', ['channel' => 'doctrine_phpcr']);

    $services->set('doctrine_phpcr.data_collector', \Doctrine\Bundle\PHPCRBundle\DataCollector\PHPCRDataCollector::class)
        ->private()
        ->args([service('doctrine_phpcr')])
        ->tag('data_collector', ['template' => '@DoctrinePHPCR/Collector/phpcr', 'id' => 'phpcr', 'priority' => 247]);

    $services->set('doctrine_phpcr.credentials', \PHPCR\SimpleCredentials::class)
        ->private()
        ->args([
            '',
            '',
        ]);

    $services->set('doctrine_phpcr', \Doctrine\Bundle\PHPCRBundle\ManagerRegistry::class)
        ->public()
        ->args([
            service('service_container'),
            '%doctrine_phpcr.sessions%',
            '%doctrine_phpcr.odm.document_managers%',
            '%doctrine_phpcr.default_session%',
            '%doctrine_phpcr.odm.default_document_manager%',
            \Doctrine\Common\Proxy\Proxy::class,
        ]);

    $services->set('form.type.phpcr.reference', \Doctrine\Bundle\PHPCRBundle\Form\Type\PHPCRReferenceType::class)
        ->args([service('doctrine_phpcr.session')->nullOnInvalid()])
        ->tag('form.type', ['alias' => 'phpcr_reference']);

    $services->set('doctrine_phpcr.console_dumper', \PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper::class);

    $services->set('doctrine_phpcr.initializer_manager', \Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager::class)
        ->public()
        ->args([service('doctrine_phpcr')]);
};
