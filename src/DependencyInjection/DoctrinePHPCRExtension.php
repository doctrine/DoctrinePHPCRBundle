<?php

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistryInterface;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPCR\SessionInterface;
use Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class DoctrinePHPCRExtension extends AbstractDoctrineExtension
{
    /**
     * @var string
     */
    private $defaultSession;

    /**
     * @var string[]
     */
    private $sessions = [];

    /**
     * @var XmlFileLoader
     */
    private $loader;

    /**
     * @var bool
     */
    private $disableProxyWarmer = false;

    /**
     * Whether the schema listener service has been loaded already.
     *
     * This is done the first time a session with jackalope-doctrine-dbal is encountered.
     */
    private $dbalSchemaListenerLoaded = false;

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $this->loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->loader->load('phpcr.xml');
        $this->loader->load('commands.xml');

        $managerRegistryServiceId = 'doctrine_phpcr';
        if (!empty($config['manager_registry_service_id'])) {
            $managerRegistryServiceId = $config['manager_registry_service_id'];
            $container->setAlias('doctrine_phpcr', new Alias($config['manager_registry_service_id'], true));
        }
        $container->setAlias(ManagerRegistryInterface::class, new Alias($managerRegistryServiceId, true));

        $parameters = [
            'workspace_dir',
            'jackrabbit_jar',
            'dump_max_line_length',
        ];

        foreach ($parameters as $param) {
            if (isset($config[$param])) {
                $container->setParameter('doctrine_phpcr.'.$param, $config[$param]);
            }
        }

        if (!empty($config['session'])) {
            $this->sessionLoad($config['session'], $container);
        }

        if (!empty($config['odm'])) {
            if (empty($this->sessions)) {
                throw new InvalidArgumentException('You did not configure a session for the document managers');
            }
            $this->loadOdm($config['odm'], $container);

            if ($this->disableProxyWarmer) {
                $container->removeDefinition('doctrine_phpcr.odm.proxy_cache_warmer');
            }
        }
        $this->loadTypeGuess($config, $container);
    }

    private function loadTypeGuess($config, ContainerBuilder $container): void
    {
        $types = [];

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['BurgovKeyValueFormBundle'])) {
            $types['assoc'] = ['burgov_key_value' => ['value_type' => 'text']];
        }

        $container->setParameter('doctrine_phpcr.form.type_guess', $types);
    }

    private function sessionLoad($config, ContainerBuilder $container): void
    {
        $sessions = $loaded = [];
        foreach ($config['sessions'] as $name => $session) {
            if (empty($config['default_session'])) {
                $config['default_session'] = $name;
            }

            $session['name'] = $name;
            $session['service_name'] = $sessions[$name] = sprintf('doctrine_phpcr.%s_session', $name);

            $type = $session['backend']['type'];
            switch ($type) {
                case 'prismic':
                case 'doctrinedbal':
                case 'jackrabbit':
                    if (empty($loaded['jackalope'])) {
                        $this->loader->load('jackalope.xml');
                        $loaded['jackalope'] = true;
                    }
                    $this->loadJackalopeSession($session, $container, $type);
                    $this->loadJackalopeSession($session, $container, $type, true);

                    break;
                default:
                    throw new InvalidArgumentException(sprintf('You set an unsupported transport type "%s" for session "%s"', $type, $name));
            }
        }

        $container->setParameter('doctrine_phpcr.sessions', $sessions);

        // no sessions configured
        if (empty($config['default_session'])) {
            $container->setParameter('doctrine_phpcr.default_session', null);

            return;
        }

        if (empty($sessions[$config['default_session']])) {
            throw new InvalidConfigurationException(sprintf("Default session is configured to '%s' which does not match any configured session name: %s", $config['default_session'], implode(', ', array_keys($sessions))));
        }
        $this->defaultSession = $config['default_session'];
        $this->sessions = $sessions;
        $container->setParameter('doctrine_phpcr.default_session', $config['default_session']);
        $container->setAlias('doctrine_phpcr.session', new Alias($sessions[$config['default_session']], true));
        $container->setAlias(SessionInterface::class, new Alias($sessions[$config['default_session']], true));
    }

    private function loadJackalopeSession(array $session, ContainerBuilder $container, $type, $admin = false): void
    {
        $serviceNamePrefix = $admin ? '.admin' : '';
        $backendParameters = [];
        switch ($type) {
            case 'doctrinedbal':
                $connectionName = !empty($session['backend']['connection'])
                    ? $session['backend']['connection']
                    : null
                ;

                if (!$this->dbalSchemaListenerLoaded) {
                    $this->loader->load('jackalope_doctrine_dbal.xml');
                    $this->dbalSchemaListenerLoaded = true;
                }

                $schemaListenerDefinition = $container->getDefinition('doctrine_phpcr.jackalope_doctrine_dbal.schema_listener');

                $eventListenerOptions = [
                    'connection' => $connectionName,
                    'event' => 'postGenerateSchema',
                    'lazy' => true,
                ];

                $schemaListenerTags = $schemaListenerDefinition->getTag('doctrine.event_listener');

                if (!\in_array($eventListenerOptions, $schemaListenerTags)) {
                    $schemaListenerDefinition->addTag('doctrine.event_listener', $eventListenerOptions);
                }

                $connectionTargetName = $connectionName
                    ? sprintf('doctrine.dbal.%s_connection', $connectionName)
                    : 'database_connection'
                ;
                $connectionAliasName = sprintf('doctrine_phpcr%s.jackalope_doctrine_dbal.%s_connection', $serviceNamePrefix, $session['name']);
                $container->setAlias($connectionAliasName, new Alias($connectionTargetName, true));
                // If default connection does not exist set the first doctrine_dbal connection as default
                $defaultConnectionName = sprintf('doctrine_phpcr%s.jackalope_doctrine_dbal.default_connection', $serviceNamePrefix);
                if (!$container->hasAlias($defaultConnectionName)) {
                    $container->setAlias($defaultConnectionName, $connectionAliasName);
                }

                $backendParameters['jackalope.doctrine_dbal_connection'] = new Reference($connectionAliasName);
                if (false === $admin && isset($session['backend']['caches'])) {
                    foreach ($session['backend']['caches'] as $key => $cache) {
                        $backendParameters['jackalope.data_caches'][$key] = new Reference($cache);
                    }
                }

                break;
            case 'prismic':
                $backendParameters['jackalope.prismic_uri'] = $session['backend']['url'];

                break;
            case 'jackrabbit':
                $backendParameters['jackalope.jackrabbit_uri'] = $session['backend']['url'];

                break;
        }

        // pipe additional parameters unchanged to jackalope
        $backendParameters += $session['backend']['parameters'];
        if (\array_key_exists('curl_options', $session['backend']) && \count($session['backend']['curl_options'])) {
            $curlOptions = [];
            foreach ($session['backend']['curl_options'] as $option => $value) {
                if (!\is_int($option)) {
                    $option = \constant($option);
                }

                $curlOptions[$option] = $value;
            }
            $backendParameters['jackalope.jackrabbit_curl_options'] = $curlOptions;
        }

        // only set this default here when we know we are jackalope
        if (!isset($backendParameters['jackalope.check_login_on_server'])) {
            $backendParameters['jackalope.check_login_on_server'] = false;
        }

        if ('doctrinedbal' === $type && $backendParameters['jackalope.check_login_on_server']) {
            $this->disableProxyWarmer = true;
        }

        if (isset($session['backend']['factory'])) {
            /*
             * If it is a class, pass the name as is, else assume it is
             * a service id and get a reference to it
             */
            if (class_exists($session['backend']['factory'])) {
                $backendParameters['jackalope.factory'] = $session['backend']['factory'];
            } else {
                $backendParameters['jackalope.factory'] = new Reference($session['backend']['factory']);
            }
        }

        $logger = null;
        if (!empty($session['backend']['logging'])) {
            $logger = new Reference('doctrine_phpcr.logger');
        }
        if (!empty($session['backend']['profiling'])) {
            $profilingLoggerId = 'doctrine_phpcr.logger.profiling.'.$session['name'];
            $profilingLoggerDef = new ChildDefinition('doctrine_phpcr.logger.profiling');

            if ($session['backend']['backtrace']) {
                $profilingLoggerDef->addMethodCall('enableBacktrace');
            }

            $container->setDefinition($profilingLoggerId, $profilingLoggerDef);
            $profilerLogger = new Reference($profilingLoggerId);
            $container->getDefinition('doctrine_phpcr.data_collector')->addMethodCall('addLogger', [$session['name'], $profilerLogger]);

            $stopWatchLoggerId = 'doctrine_phpcr.logger.stop_watch.'.$session['name'];
            $stopWatchLoggerDefinition = new ChildDefinition('doctrine_phpcr.logger.stop_watch');
            $container->setDefinition($stopWatchLoggerId, $stopWatchLoggerDefinition);
            $stopWatchLogger = new Reference($stopWatchLoggerId);

            $chainLogger = new ChildDefinition('doctrine_phpcr.logger.chain');
            $chainLogger->addMethodCall('addLogger', [$profilerLogger]);
            $chainLogger->addMethodCall('addLogger', [$stopWatchLogger]);

            if (null !== $logger) {
                $chainLogger->addMethodCall('addLogger', [$logger]);
            }

            $loggerId = 'doctrine_phpcr.logger.chain.'.$session['name'];
            $container->setDefinition($loggerId, $chainLogger);
            $logger = new Reference($loggerId);
        }

        if ($logger) {
            $backendParameters['jackalope.logger'] = $logger;
        }

        $repositoryFactory = new ChildDefinition('doctrine_phpcr.jackalope.repository.factory.'.$type);
        $factory = $container
            ->setDefinition(sprintf('doctrine_phpcr%s.jackalope.repository.%s', $serviceNamePrefix, $session['name']), $repositoryFactory)
        ;
        $factory->replaceArgument(0, $backendParameters);

        $username = $admin && $session['admin_username'] ? $session['admin_username'] : $session['username'];
        $password = $admin && $session['admin_password'] ? $session['admin_password'] : $session['password'];
        $credentials = new ChildDefinition('doctrine_phpcr.credentials');
        $credentialsServiceId = sprintf('doctrine_phpcr%s.%s_credentials', $serviceNamePrefix, $session['name']);
        $container
            ->setDefinition($credentialsServiceId, $credentials)
            ->replaceArgument(0, $username)
            ->replaceArgument(1, $password)
        ;

        $definition = new ChildDefinition('doctrine_phpcr.jackalope.session');
        $factoryServiceId = sprintf('doctrine_phpcr%s.jackalope.repository.%s', $serviceNamePrefix, $session['name']);
        $definition->setFactory([
            new Reference($factoryServiceId),
            'login',
        ]);

        $workspace = $admin ? null : $session['workspace'];
        $definition
            ->replaceArgument(0, new Reference($credentialsServiceId))
            ->replaceArgument(1, $workspace)
        ;

        $serviceName = sprintf('doctrine_phpcr%s.%s_session', $serviceNamePrefix, $session['name']);
        $container->setDefinition($serviceName, $definition)->setPublic(true);

        foreach ($session['options'] as $key => $value) {
            $definition->addMethodCall('setSessionOption', [$key, $value]);
        }

        $eventManagerServiceId = sprintf('doctrine_phpcr%s.%s_session.event_manager', $serviceNamePrefix, $session['name']);
        $eventManagerDefinition = new ChildDefinition('doctrine_phpcr.session.event_manager');
        $container->setDefinition($eventManagerServiceId, $eventManagerDefinition);
    }

    private function loadOdm(array $config, ContainerBuilder $container): void
    {
        $this->loader->load('odm.xml');
        $this->loadOdmLocales($config, $container);
        $config['document_managers'] = $this->fixManagersAutoMappings($config['document_managers'], $container->getParameter('kernel.bundles'));

        $documentManagers = [];
        foreach ($config['document_managers'] as $name => $documentManager) {
            if (empty($config['default_document_manager'])) {
                $config['default_document_manager'] = $name;
            }

            $documentManager['name'] = $name;
            $documentManager['service_name'] = $documentManagers[$name] = sprintf('doctrine_phpcr.odm.%s_document_manager', $name);
            $this->loadOdmDocumentManager($documentManager, $container);
        }

        $container->setParameter('doctrine_phpcr.odm.document_managers', $documentManagers);

        // no document manager configured
        if (empty($config['default_document_manager'])) {
            return;
        }

        $container->setParameter('doctrine_phpcr.odm.default_document_manager', $config['default_document_manager']);
        $container->setAlias('doctrine_phpcr.odm.document_manager', new Alias($documentManagers[$config['default_document_manager']], true));
        $container->setAlias(DocumentManagerInterface::class, new Alias($documentManagers[$config['default_document_manager']]));

        $options = ['auto_generate_proxy_classes', 'proxy_dir', 'proxy_namespace'];
        foreach ($options as $key) {
            $container->setParameter('doctrine_phpcr.odm.'.$key, $config[$key]);
        }

        if (!$config['namespaces']['translation']['alias']) {
            throw new InvalidArgumentException(
                'Translation namespace alias must not be empty'
            );
        }

        $container->setParameter('doctrine_phpcr.odm.namespaces.translation.alias', $config['namespaces']['translation']['alias']);
    }

    private function loadOdmLocales(array $config, ContainerBuilder $container): void
    {
        $localeChooser = $config['locale_chooser'];

        if (empty($config['locales']) && null === $config['locale_chooser']) {
            return;
        }

        if (!empty($config['locales'])) {
            $this->loader->load('odm_multilang.xml');

            foreach ($config['locales'] as $locale => $fallbacks) {
                if (\in_array($locale, $fallbacks)) {
                    throw new InvalidArgumentException(sprintf('The fallbacks for locale %s contain the locale itself.', $locale));
                }
                if (\count($fallbacks) !== \count(array_unique($fallbacks))) {
                    throw new InvalidArgumentException(sprintf('Duplicate locale in the fallbacks for locale %s.', $locale));
                }
            }

            $container->setParameter('doctrine_phpcr.odm.locales', $config['locales']);
            $container->setParameter('doctrine_phpcr.odm.allowed_locales', array_keys($config['locales']));
            if (isset($config['default_locale']) && null !== $config['default_locale']) {
                $defaultLocale = $config['default_locale'];
                if (!isset($config['locales'][$defaultLocale])) {
                    throw new InvalidConfigurationException('Default locale must be listed in locale list');
                }
            } else {
                $defaultLocale = key($config['locales']);
            }
            $container->setParameter('doctrine_phpcr.odm.default_locale', $defaultLocale);
            $container->setParameter('doctrine_phpcr.odm.locale_fallback', $config['locale_fallback']);

            $localeChooser = $localeChooser ?: 'doctrine_phpcr.odm.locale_chooser';
        }

        // only set the locale chooser if it has been explicitly configured or implicitly
        // set by configuring the locales node.
        if (null !== $localeChooser) {
            $dm = $container->getDefinition('doctrine_phpcr.odm.document_manager.abstract');
            $dm->addMethodCall('setLocaleChooserStrategy', [new Reference($localeChooser)]);
        }
    }

    private function loadOdmDocumentManager(array $documentManager, ContainerBuilder $container): void
    {
        $odmConfigDefTemplate = empty($documentManager['configuration_id']) ? 'doctrine_phpcr.odm.configuration' : $documentManager['configuration_id'];
        $odmConfigDefDefinition = new ChildDefinition($odmConfigDefTemplate);
        $odmConfigDef = $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name']), $odmConfigDefDefinition);

        $this->loadOdmDocumentManagerMappingInformation($documentManager, $odmConfigDef, $container);
        $this->loadOdmCacheDrivers($documentManager, $container);

        $methods = [
            'setMetadataCacheImpl' => [new Reference(sprintf('doctrine_phpcr.odm.%s_metadata_cache', $documentManager['name']))],
            'setMetadataDriverImpl' => [new Reference('doctrine_phpcr.odm.'.$documentManager['name'].'_metadata_driver'), false],
            'setProxyDir' => ['%doctrine_phpcr.odm.proxy_dir%'],
            'setProxyNamespace' => ['%doctrine_phpcr.odm.proxy_namespace%'],
            'setAutoGenerateProxyClasses' => ['%doctrine_phpcr.odm.auto_generate_proxy_classes%'],
        ];

        $methods['setClassMetadataFactoryName'] = [$documentManager['class_metadata_factory_name']];
        $methods['setDefaultRepositoryClassName'] = [$documentManager['default_repository_class']];

        if ($documentManager['repository_factory']) {
            $methods['setRepositoryFactory'] = [new Reference($documentManager['repository_factory'])];
        }

        foreach ($methods as $method => $args) {
            $odmConfigDef->addMethodCall($method, $args);
        }

        if (!isset($documentManager['session'])) {
            $documentManager['session'] = $this->defaultSession;
        }

        if (!isset($this->sessions[$documentManager['session']])) {
            throw new InvalidArgumentException(sprintf("You have configured a non existent session '%s' for the document manager '%s'", $documentManager['session'], $documentManager['name']));
        }

        $abstractDocumentManagerDefinition = new ChildDefinition('doctrine_phpcr.odm.document_manager.abstract');
        $documentManagerDefinition = $container
            ->setDefinition($documentManager['service_name'], $abstractDocumentManagerDefinition)
            ->setArguments([
                new Reference(sprintf('doctrine_phpcr.%s_session', $documentManager['session'])),
                new Reference(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name'])),
                new Reference(sprintf('doctrine_phpcr.%s_session.event_manager', $documentManager['session'])),
            ])
            ->setPublic(true);

        foreach ([
            'child' => 'doctrine_phpcr.odm.translation.strategy.child',
            'attribute' => 'doctrine_phpcr.odm.translation.strategy.attribute',
        ] as $name => $strategyTemplateId) {
            $strategyId = sprintf('doctrine_phpcr.odm.%s.translation.strategy.%s', $documentManager['name'], $name);
            $strategyDefinition = new ChildDefinition($strategyTemplateId);
            $container->setDefinition($strategyId, $strategyDefinition);

            $strategyDefinition->replaceArgument(0, new Reference($documentManager['service_name']));
            $documentManagerDefinition->addMethodCall('setTranslationStrategy', [$name, new Reference($strategyId)]);
        }
    }

    private function loadOdmDocumentManagerMappingInformation(array $documentManager, Definition $odmConfig, ContainerBuilder $container): void
    {
        // reset state of drivers and alias map. They are only used by this methods and children.
        $this->drivers = [];
        $this->aliasMap = [];
        $this->bundleDirs = [];

        if (!class_exists(Generic::class)) {
            throw new \RuntimeException('PHPCR ODM is activated in the config but does not seem loadable.');
        }

        $class = new \ReflectionClass(Generic::class);

        $documentManager['mappings']['__PHPCRODM__'] = [
            'dir' => \dirname($class->getFileName()),
            'type' => 'annotation',
            'prefix' => 'Doctrine\ODM\PHPCR\Document',
            'is_bundle' => false,
            'mapping' => true,
        ];
        $this->loadMappingInformation($documentManager, $container);
        $this->registerMappingDrivers($documentManager, $container);

        $odmConfig->addMethodCall('setDocumentNamespaces', [$this->aliasMap]);
    }

    /**
     * Loads a configured document managers cache drivers.
     *
     * @param array            $documentManager a configured ORM document manager
     * @param ContainerBuilder $container       A ContainerBuilder instance
     */
    private function loadOdmCacheDrivers(array $documentManager, ContainerBuilder $container): void
    {
        $this->loadCacheDriver('metadata_cache', $documentManager['name'], $documentManager['metadata_cache_driver'], $container);
    }

    /**
     * Prevent calling the generic method from the Symfony bridge.
     */
    protected function loadObjectManagerCacheDriver(array $objectManager, ContainerBuilder $container, $cacheName): void
    {
        $this->loadCacheDriver($cacheName, $objectManager['name'], $objectManager[$cacheName.'_driver'], $container);
    }

    protected function loadCacheDriver($cacheName, $objectManagerName, array $cacheDriver, ContainerBuilder $container): string
    {
        $aliasId = $this->getObjectManagerElementName(sprintf('%s_%s', $objectManagerName, $cacheName));

        switch ($cacheDriver['type']) {
            case 'service':
                $serviceId = $cacheDriver['id'];
                break;

            case 'array':
                $serviceId = $this->createArrayAdapterCachePool($container, $objectManagerName, $cacheName);
                break;

            default:
                throw new InvalidArgumentException(sprintf(
                    'Unknown cache of type "%s" configured for cache "%s" in entity manager "%s".',
                    $cacheDriver['type'],
                    $cacheName,
                    $objectManagerName
                ));
        }

        $container->setAlias($aliasId, new Alias($serviceId, false));

        return $aliasId;
    }

    private function createArrayAdapterCachePool(ContainerBuilder $container, string $objectManagerName, string $cacheName): string
    {
        $id = sprintf('cache.doctrine.phpcr_odm.%s.%s', $objectManagerName, str_replace('_cache', '', $cacheName));

        $poolDefinition = $container->register($id, ArrayAdapter::class);
        $poolDefinition->addTag('cache.pool');
        $container->setDefinition($id, $poolDefinition);

        return $id;
    }

    protected function getMappingObjectDefaultName(): string
    {
        return 'Document';
    }

    protected function getMappingResourceConfigDirectory(string $bundleDir = null): string
    {
        return 'Resources/config/doctrine';
    }

    protected function getMappingResourceExtension(): string
    {
        return 'phpcr';
    }

    public function getNamespace()
    {
        return 'http://doctrine-project.org/schema/symfony-dic/odm/phpcr';
    }

    protected function getMetadataDriverClass(string $driverType): string
    {
        return '%'.$this->getObjectManagerElementName('metadata.'.$driverType.'.class%');
    }

    protected function getObjectManagerElementName(string $name): string
    {
        return 'doctrine_phpcr.odm.'.$name;
    }
}
