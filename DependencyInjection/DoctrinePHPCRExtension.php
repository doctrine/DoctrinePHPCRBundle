<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension;
use Doctrine\ODM\PHPCR\Version;

/**
 * PHPCR Extension.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DoctrinePHPCRExtension extends AbstractDoctrineExtension
{
    private $defaultSession;
    private $sessions = array();
    private $bundleDirs = array();
    /** @var XmlFileLoader */
    private $loader;
    private $disableProxyWarmer = false;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);
        $this->loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->loader->load('phpcr.xml');

        if (!empty($config['manager_registry_service_id'])) {
            $container->setAlias('doctrine_phpcr', new Alias($config['manager_registry_service_id']));
        }

        $parameters = array(
            'workspace_dir',
            'jackrabbit_jar',
            'dump_max_line_length',
        );

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

    private function loadTypeGuess($config, ContainerBuilder $container)
    {
        $types = array();

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['BurgovKeyValueFormBundle'])) {
            $types['assoc'] = array('burgov_key_value' => array('value_type' => 'text'));
        }

        $container->setParameter('doctrine_phpcr.form.type_guess', $types);
    }

    private function sessionLoad($config, ContainerBuilder $container)
    {
        $sessions = $loaded = array();
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

                        // TODO: move the following code block back into the XML file when we drop support for symfony <2.6
                        $jackalopeTransports = array('prismic', 'doctrinedbal', 'jackrabbit');
                        foreach ($jackalopeTransports as $transport) {
                            $factoryServiceId = sprintf('doctrine_phpcr.jackalope.repository.factory.service.%s', $transport);
                            $factoryService = $container->getDefinition(sprintf('doctrine_phpcr.jackalope.repository.factory.%s', $transport));
                            if (method_exists($factoryService, 'setFactory')) {
                                $factoryService->setFactory(array(
                                    new Reference($factoryServiceId),
                                    'getRepository',
                                ));
                            } else {
                                $factoryService->setFactoryService($factoryServiceId);
                                $factoryService->setFactoryMethod('getRepository');
                            }
                        }

                        $loaded['jackalope'] = true;
                    }
                    $this->loadJackalopeSession($session, $container, $type);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('You set an unsupported transport type "%s" for session "%s"', $type, $name));
            }
        }

        $container->setParameter('doctrine_phpcr.sessions', $sessions);

        // no sessions configured
        if (empty($config['default_session'])) {
            return;
        }

        if (empty($sessions[$config['default_session']])) {
            throw new InvalidConfigurationException(sprintf("Default session is configured to '%s' which does not match any configured session name: %s", $config['default_session'], implode(', ', array_keys($sessions))));
        }
        $this->defaultSession = $config['default_session'];
        $this->sessions = $sessions;
        $container->setParameter('doctrine_phpcr.default_session', $config['default_session']);
        $container->setAlias('doctrine_phpcr.session', $sessions[$config['default_session']]);
    }

    private function loadJackalopeSession(array $session, ContainerBuilder $container, $type, $admin = false)
    {
        $serviceNamePrefix = $admin ? '.admin' : '';
        $backendParameters = array();
        switch ($type) {
            case 'doctrinedbal':
                $connectionName = !empty($session['backend']['connection'])
                    ? $session['backend']['connection']
                    : null
                ;
                $connectionService = $connectionName
                    ? sprintf('doctrine.dbal.%s_connection', $connectionName)
                    : 'database_connection'
                ;
                $connectionService = new Alias($connectionService, true);
                $connectionAliasName = sprintf('doctrine_phpcr.jackalope_doctrine_dbal%s.%s_connection', $serviceNamePrefix, $session['name']);
                $container->setAlias($connectionAliasName, $connectionService);

                $backendParameters['jackalope.doctrine_dbal_connection'] = new Reference($connectionAliasName);
                $container
                    ->getDefinition('doctrine_phpcr.jackalope_doctrine_dbal.schema_listener')
                    ->addTag('doctrine.event_listener', array(
                        'connection' => $connectionName,
                        'event' => 'postGenerateSchema',
                        'lazy' => true,
                    ))
                ;
                if (isset($session['backend']['caches'])) {
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
        // only set this default here when we know we are jackalope
        if (!isset($backendParameters['jackalope.check_login_on_server'])) {
            $backendParameters['jackalope.check_login_on_server'] = $container->getParameter('kernel.debug');
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
            $profilingLoggerDef = new DefinitionDecorator('doctrine_phpcr.logger.profiling');

            if ($session['backend']['backtrace']) {
                $profilingLoggerDef->addMethodCall('enableBacktrace');
            }

            $container->setDefinition($profilingLoggerId, $profilingLoggerDef);
            $profilerLogger = new Reference($profilingLoggerId);
            $container->getDefinition('doctrine_phpcr.data_collector')->addMethodCall('addLogger', array($session['name'], $profilerLogger));

            $stopWatchLoggerId = 'doctrine_phpcr.logger.stop_watch.'.$session['name'];
            $container->setDefinition($stopWatchLoggerId, new DefinitionDecorator('doctrine_phpcr.logger.stop_watch'));
            $stopWatchLogger = new Reference($stopWatchLoggerId);

            $chainLogger = new DefinitionDecorator('doctrine_phpcr.logger.chain');
            $chainLogger->addMethodCall('addLogger', array($profilerLogger));
            $chainLogger->addMethodCall('addLogger', array($stopWatchLogger));

            if (null !== $logger) {
                $chainLogger->addMethodCall('addLogger', array($logger));
            }

            $loggerId = 'doctrine_phpcr.logger.chain.'.$session['name'];
            $container->setDefinition($loggerId, $chainLogger);
            $logger = new Reference($loggerId);
        }

        if ($logger) {
            $backendParameters['jackalope.logger'] = $logger;
        }

        $repositoryFactory = new DefinitionDecorator('doctrine_phpcr.jackalope.repository.factory.'.$type);
        $factory = $container
            ->setDefinition(sprintf('doctrine_phpcr.jackalope.repository%s.%s', $serviceNamePrefix, $session['name']), $repositoryFactory)
        ;
        $factory->replaceArgument(0, $backendParameters);

        $username = $admin && $session['admin_username'] ? $session['admin_username'] : $session['username'];
        $password = $admin && $session['admin_password'] ? $session['admin_password'] : $session['password'];
        $credentials = new DefinitionDecorator('doctrine_phpcr.credentials');
        $credentialsServiceId = sprintf('doctrine_phpcr%s.%s_credentials', $serviceNamePrefix, $session['name']);
        $container
            ->setDefinition($credentialsServiceId, $credentials)
            ->replaceArgument(0, $username)
            ->replaceArgument(1, $password)
        ;

        // TODO: move the following code block back into the XML file when we drop support for symfony <2.6
        $definition = new DefinitionDecorator('doctrine_phpcr.jackalope.session');
        $factoryServiceId = sprintf('doctrine_phpcr%s.jackalope.repository.%s', $serviceNamePrefix, $session['name']);
        if (method_exists($definition, 'setFactory')) {
            $definition->setFactory(array(
                new Reference($factoryServiceId),
                'login',
            ));
        } else {
            $definition->setFactoryService($factoryServiceId);
            $definition->setFactoryMethod('login');
        }

        $workspace = $admin ? null : $session['workspace'];
        $definition
            ->replaceArgument(0, new Reference($credentialsServiceId))
            ->replaceArgument(1, $workspace)
        ;

        $serviceName = sprintf('doctrine_phpcr%s.%s_session', $serviceNamePrefix, $session['name']);
        $container->setDefinition($serviceName, $definition);

        foreach ($session['options'] as $key => $value) {
            $definition->addMethodCall('setSessionOption', array($key, $value));
        }

        $eventManagerServiceId = sprintf('doctrine_phpcr%s.%s_session.event_manager', $serviceNamePrefix, $session['name']);
        $container->setDefinition($eventManagerServiceId, new DefinitionDecorator('doctrine_phpcr.session.event_manager'));
    }

    private function loadOdm(array $config, ContainerBuilder $container)
    {
        $this->loader->load('odm.xml');
        $this->loadOdmLocales($config, $container);

        // BC logic to handle DoctrineBridge < 2.6
        if (!method_exists($this, 'fixManagersAutoMappings')) {
            foreach ($config['document_managers'] as $documentManager) {
                if ($documentManager['auto_mapping'] && count($config['document_managers']) > 1) {
                    throw new LogicException('You cannot enable "auto_mapping" when several PHPCR document managers are defined.');
                }
            }
        } else {
            $config['document_managers'] = $this->fixManagersAutoMappings($config['document_managers'], $container->getParameter('kernel.bundles'));
        }

        $documentManagers = array();
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
        $container->setAlias('doctrine_phpcr.odm.document_manager', $documentManagers[$config['default_document_manager']]);

        $options = array('auto_generate_proxy_classes', 'proxy_dir', 'proxy_namespace');
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

    private function loadOdmLocales(array $config, ContainerBuilder $container)
    {
        $localeChooser = $config['locale_chooser'];

        if (empty($config['locales']) && null === $config['locale_chooser']) {
            return;
        }

        if (!empty($config['locales'])) {
            $this->loader->load('odm_multilang.xml');

            foreach ($config['locales'] as $locale => $fallbacks) {
                if (false !== array_search($locale, $fallbacks)) {
                    throw new InvalidArgumentException(sprintf('The fallbacks for locale %s contain the locale itself.', $locale));
                }
                if (count($fallbacks) !== count(array_unique($fallbacks))) {
                    throw new InvalidArgumentException(sprintf('Duplicate locale in the fallbacks for locale %s.', $locale));
                }
            }

            $container->setParameter('doctrine_phpcr.odm.locales', $config['locales']);
            $container->setParameter('doctrine_phpcr.odm.allowed_locales', array_keys($config['locales']));
            $container->setParameter('doctrine_phpcr.odm.default_locale', key($config['locales']));
            $container->setParameter('doctrine_phpcr.odm.locale_fallback', $config['locale_fallback'] == 'hardcoded' ? null : $config['locale_fallback']);

            $localeChooser = $localeChooser ?: 'doctrine_phpcr.odm.locale_chooser';
        }

        // only set the locale chooser if it has been explicitly configured or implicitly
        // set by configuring the locales node.
        if (null !== $localeChooser) {
            $dm = $container->getDefinition('doctrine_phpcr.odm.document_manager.abstract');
            $dm->addMethodCall('setLocaleChooserStrategy', array(new Reference($localeChooser)));
        }
    }

    private function loadOdmDocumentManager(array $documentManager, ContainerBuilder $container)
    {
        $odmConfigDefTemplate = empty($documentManager['configuration_id']) ? 'doctrine_phpcr.odm.configuration' : $documentManager['configuration_id'];
        $odmConfigDef = $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name']), new DefinitionDecorator($odmConfigDefTemplate));

        $this->loadOdmDocumentManagerMappingInformation($documentManager, $odmConfigDef, $container);
        $this->loadOdmCacheDrivers($documentManager, $container);

        $methods = array(
            'setMetadataCacheImpl' => array(new Reference(sprintf('doctrine_phpcr.odm.%s_metadata_cache', $documentManager['name']))),
            'setMetadataDriverImpl' => array(new Reference('doctrine_phpcr.odm.'.$documentManager['name'].'_metadata_driver'), false),
            'setProxyDir' => array('%doctrine_phpcr.odm.proxy_dir%'),
            'setProxyNamespace' => array('%doctrine_phpcr.odm.proxy_namespace%'),
            'setAutoGenerateProxyClasses' => array('%doctrine_phpcr.odm.auto_generate_proxy_classes%'),
        );

        if (version_compare(Version::VERSION, '1.1.0-DEV') >= 0) {
            $methods['setClassMetadataFactoryName'] = array($documentManager['class_metadata_factory_name']);
            $methods['setDefaultRepositoryClassName'] = array($documentManager['default_repository_class']);

            if ($documentManager['repository_factory']) {
                $methods['setRepositoryFactory'] = array(new Reference($documentManager['repository_factory']));
            }
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

        $documentManagerDefinition = $container
            ->setDefinition($documentManager['service_name'], new DefinitionDecorator('doctrine_phpcr.odm.document_manager.abstract'))
            ->setArguments(array(
                new Reference(sprintf('doctrine_phpcr.%s_session', $documentManager['session'])),
                new Reference(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name'])),
                new Reference(sprintf('doctrine_phpcr.%s_session.event_manager', $documentManager['session'])),
            ));

        foreach (array(
            'child' => 'doctrine_phpcr.odm.translation.strategy.child',
            'attribute' => 'doctrine_phpcr.odm.translation.strategy.attribute',
        ) as $name => $strategyTemplateId) {
            $strategyId = sprintf('doctrine_phpcr.odm.%s.translation.strategy.%s', $documentManager['name'], $name);
            $strategyDefinition = new DefinitionDecorator($strategyTemplateId);
            $container->setDefinition($strategyId, $strategyDefinition);

            $strategyDefinition->replaceArgument(0, new Reference($documentManager['service_name']));
            $documentManagerDefinition->addMethodCall('setTranslationStrategy', array($name, new Reference($strategyId)));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getMappingDriverBundleConfigDefaults(array $bundleConfig, \ReflectionClass $bundle, ContainerBuilder $container)
    {
        $this->bundleDirs[] = dirname($bundle->getFileName());

        return parent::getMappingDriverBundleConfigDefaults($bundleConfig, $bundle, $container);
    }

    protected function loadOdmDocumentManagerMappingInformation(array $documentManager, Definition $odmConfig, ContainerBuilder $container)
    {
        // reset state of drivers and alias map. They are only used by this methods and children.
        $this->drivers = array();
        $this->aliasMap = array();
        $this->bundleDirs = array();

        if (!class_exists('Doctrine\ODM\PHPCR\Document\Generic')) {
            throw new \RuntimeException('PHPCR ODM is activated in the config but does not seem loadable.');
        }

        $class = new \ReflectionClass('Doctrine\ODM\PHPCR\Document\Generic');

        $documentManager['mappings']['__PHPCRODM__'] = array(
            'dir' => dirname($class->getFileName()),
            'type' => 'annotation',
            'prefix' => 'Doctrine\ODM\PHPCR\Document',
            'is_bundle' => false,
            'mapping' => true,
        );
        $this->loadMappingInformation($documentManager, $container);
        $this->registerMappingDrivers($documentManager, $container);

        $odmConfig->addMethodCall('setDocumentNamespaces', array($this->aliasMap));
    }

    /**
     * Loads a configured document managers cache drivers.
     *
     * @param array            $documentManager A configured ORM document manager.
     * @param ContainerBuilder $container       A ContainerBuilder instance
     */
    protected function loadOdmCacheDrivers(array $documentManager, ContainerBuilder $container)
    {
        $this->loadObjectManagerCacheDriver($documentManager, $container, 'metadata_cache');
    }

    /**
     * {@inheritDoc}
     */
    protected function getObjectManagerElementName($name)
    {
        return 'doctrine_phpcr.odm.'.$name;
    }

    /**
     * {@inheritDoc}
     */
    protected function getMappingObjectDefaultName()
    {
        return 'Document';
    }

    /**
     * {@inheritDoc}
     */
    protected function getMappingResourceConfigDirectory()
    {
        return 'Resources/config/doctrine';
    }

    /**
     * {@inheritDoc}
     */
    protected function getMappingResourceExtension()
    {
        return 'phpcr';
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return 'http://doctrine-project.org/schema/symfony-dic/odm/phpcr';
    }
}
