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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * PHPCR Extension
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DoctrinePHPCRExtension extends AbstractDoctrineExtension
{
    private $defaultSession;
    private $sessions = array();

    private $bundleDirs = array();

    public function load(array $configs, ContainerBuilder $container)
    {
        // TODO move this default to the Configuration class if somebody can figure out how to do it
        array_unshift($configs,
            array('odm' => array(
                'locales' => array(
                    'en' => array('en'),
                ),
            ),
        ));

        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        if (!empty($config['session'])) {
            $this->sessionLoad($config['session'], $container);
        }

        if (!empty($config['odm'])) {
            if (empty($this->sessions)) {
                throw new \InvalidArgumentException("You did not configure a session for the document managers");
            }
            $this->odmLoad($config['odm'], $container);
        }
    }

    private function sessionLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('phpcr.xml');

        $sessions = $loaded = array();
        foreach ($config['sessions'] as $name => $session) {
            if (empty($config['default_session'])) {
                $config['default_session'] = $name;
            }

            $session['name'] = $name;
            $session['service_name'] = $sessions[$name] = sprintf('doctrine_phpcr.%s_session', $name);

            $type = isset($session['backend']['type']) ? $session['backend']['type'] : 'jackrabbit';
            switch ($type) {
                case 'doctrinedbal':
                case 'jackrabbit':
                    if (empty($loaded['jackalope'])) {
                        $loader->load('jackalope.xml');
                        $loaded['jackalope'] = true;
                    }
                    $this->loadJackalopeSession($session, $container, $type);
                    break;
                case 'midgard':
                    if (empty($loaded['midgard'])) {
                        $loader->load('midgard.xml');
                        $loaded['midgard'] = true;

                        if (isset($session['config'])) {
                            ini_set('midgard.configuration_file', $session['config']);
                        }
                    }
                    $this->loadMidgardSession($session, $container);
                    break;
                default:
                    throw new \InvalidArgumentException("You set an unsupported transport type '$type' for session '$name'");
            }
        }

        $container->setParameter('doctrine_phpcr.sessions', $sessions);

        // no sessions configured
        if (empty($config['default_session'])) {
            return;
        }

        $this->defaultSession = $config['default_session'];
        $this->sessions = $sessions;
        $container->setParameter('doctrine_phpcr.default_session', $config['default_session']);
        $container->setAlias('doctrine_phpcr.session', $sessions[$config['default_session']]);
    }

    private function loadJackalopeSession(array $session, ContainerBuilder $container, $type)
    {
        switch ($type) {
            case 'doctrinedbal':
                if (isset($session['backend']['connection'])) {
                    $parameters['jackalope.doctrine_dbal_connection'] = new Reference($session['backend']['connection']);
                }
                break;
            case 'jackrabbit':
                if (isset($session['backend']['url'])) {
                    $parameters['jackalope.jackrabbit_uri'] = $session['backend']['url'];
                }
                if (isset($session['backend']['default_header'])) {
                    $parameters['jackalope.jackalope.default_header'] = $session['backend']['default_header'];
                }
                if (isset($session['backend']['expect'])) {
                    $parameters['jackalope.jackalope.jackrabbit_expect'] = $session['backend']['expect'];
                }
                break;
        }

        $parameters['jackalope.check_login_on_server'] = false;
        if (isset($session['backend']['check_login_on_server'])) {
            $parameters['jackalope.check_login_on_server'] = $session['backend']['check_login_on_server'];
        }
        if (isset($session['backend']['disable_stream_wrapper'])) {
            $parameters['jackalope.disable_stream_wrapper'] = $session['backend']['disable_stream_wrapper'];
        }
        if (isset($session['backend']['disable_transactions'])) {
            $parameters['jackalope.disable_transactions'] = $session['backend']['disable_transactions'];
        }

        $factory = $container
            ->setDefinition(sprintf('doctrine_phpcr.jackalope.repository.%s', $session['name']), new DefinitionDecorator('doctrine_phpcr.jackalope.repository.factory.'.$type))
        ;
        $factory->replaceArgument(0, $parameters);

        $container
            ->setDefinition(sprintf('doctrine_phpcr.%s_credentials', $session['name']), new DefinitionDecorator('doctrine_phpcr.credentials'))
            ->replaceArgument(0, $session['username'])
            ->replaceArgument(1, $session['password'])
        ;

        $container
            ->setDefinition($session['service_name'], new DefinitionDecorator('doctrine_phpcr.jackalope.session'))
            ->setFactoryService(sprintf('doctrine_phpcr.jackalope.repository.%s', $session['name']))
            ->replaceArgument(0, new Reference(sprintf('doctrine_phpcr.%s_credentials', $session['name'])))
            ->replaceArgument(1, $session['workspace'])
        ;
    }

    private function loadMidgardSession(array $session, ContainerBuilder $container)
    {
        $container
            ->setDefinition(sprintf('doctrine_phpcr.%s_credentials', $session['name']), new DefinitionDecorator('doctrine_phpcr.credentials'))
            ->replaceArgument(0, $session['username'])
            ->replaceArgument(1, $session['password'])
        ;

        $container
            ->setDefinition($session['service_name'], new DefinitionDecorator('doctrine_phpcr.midgard.session'))
            ->replaceArgument(0, new Reference(sprintf('doctrine_phpcr.%s_credentials', $session['name'])))
            ->replaceArgument(1, $session['workspace'])
        ;
    }

    private function odmLoad(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('odm.xml');

        $documentManagers = array();
        foreach ($config['document_managers'] as $name => $documentManager) {
            if (empty($config['default_document_manager'])) {
                $config['default_document_manager'] = $name;
            }

            $documentManager['name'] = $name;
            $documentManager['service_name'] = $documentManagers[$name] = sprintf('doctrine_phpcr.odm.%s_document_manager', $name);
            if ($documentManager['auto_mapping'] && count($config['document_managers']) > 1) {
                throw new \LogicException('You cannot enable "auto_mapping" when several PHPCR document managers are defined.');
            }

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
            $container->setParameter('doctrine_phpcr.odm.' . $key, $config[$key]);
        }

        $container->setParameter('doctrine_phpcr.odm.locales', $config['locales']);
    }

    private function loadOdmDocumentManager(array $documentManager, ContainerBuilder $container)
    {
        $odmConfigDef = $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name']), new DefinitionDecorator('doctrine_phpcr.odm.configuration'));

        $this->loadOdmDocumentManagerMappingInformation($documentManager, $odmConfigDef, $container);
        $this->loadOdmCacheDrivers($documentManager, $container);

        $methods = array(
            'setMetadataCacheImpl' => array(new Reference(sprintf('doctrine_phpcr.odm.%s_metadata_cache', $documentManager['name']))),
            'setMetadataDriverImpl' => array(new Reference('doctrine_phpcr.odm.' . $documentManager['name'] . '_metadata_driver'), false),
            'setProxyDir' => array('%doctrine_phpcr.odm.proxy_dir%'),
            'setProxyNamespace' => array('%doctrine_phpcr.odm.proxy_namespace%'),
            'setAutoGenerateProxyClasses' => array('%doctrine_phpcr.odm.auto_generate_proxy_classes%'),
        );
        foreach ($methods as $method => $args) {
            $odmConfigDef->addMethodCall($method, $args);
        }

        if (!isset($documentManager['session'])) {
            $documentManager['session'] = $this->defaultSession;
        }

        if (!isset($this->sessions[$documentManager['session']])) {
            throw new \InvalidArgumentException(sprintf("You have configured a non existent session '%s' for the document manager '%s'", $documentManager['session'], $documentManager['name']));
        }

        $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_session.event_manager', $documentManager['name']), new DefinitionDecorator('doctrine_phpcr.odm.document_manager.event_manager'));

        $container
            ->setDefinition($documentManager['service_name'], new DefinitionDecorator('doctrine_phpcr.odm.document_manager.abstract'))
            ->setArguments(array(
                new Reference(sprintf('doctrine_phpcr.%s_session', $documentManager['session'])),
                new Reference(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name'])),
                new Reference(sprintf('doctrine_phpcr.odm.%s_session.event_manager', $documentManager['name']))
            ))
        ;
    }

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
     * @param ContainerBuilder $container     A ContainerBuilder instance
     */
    protected function loadOdmCacheDrivers(array $documentManager, ContainerBuilder $container)
    {
        $this->loadOdmDocumentManagerCacheDriver($documentManager, $container, 'metadata_cache');
    }

    /**
     * Loads a configured document managers metadata, query or result cache driver.
     *
     * @param array            $documentManager A configured ORM document manager.
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @param string           $cacheName
     */
    protected function loadOdmDocumentManagerCacheDriver(array $documentManager, ContainerBuilder $container, $cacheName)
    {
        $cacheDriverService = sprintf('doctrine_phpcr.odm.%s_%s', $documentManager['name'], $cacheName);

        $driver = $cacheName . "_driver";
        $cacheDef = $this->getDocumentManagerCacheDefinition($documentManager, $documentManager[$driver], $container);
        $container->setDefinition($cacheDriverService, $cacheDef);
    }

    /**
     * Gets an document manager cache driver definition for caches.
     *
     * @param array            $documentManager The array configuring an document manager.
     * @param array            $cacheDriver The cache driver configuration.
     * @param ContainerBuilder $container
     * @return Definition $cacheDef
     */
    protected function getDocumentManagerCacheDefinition(array $documentManager, $cacheDriver, ContainerBuilder $container)
    {
        switch ($cacheDriver['type']) {
            case 'memcache':
                $memcacheClass = !empty($cacheDriver['class']) ? $cacheDriver['class'] : '%doctrine_phpcr.odm.cache.memcache.class%';
                $memcacheInstanceClass = !empty($cacheDriver['instance_class']) ? $cacheDriver['instance_class'] : '%doctrine_phpcr.odm.cache.memcache_instance.class%';
                $memcacheHost = !empty($cacheDriver['host']) ? $cacheDriver['host'] : '%doctrine_phpcr.odm.cache.memcache_host%';
                $memcachePort = !empty($cacheDriver['port']) ? $cacheDriver['port'] : '%doctrine_phpcr.odm.cache.memcache_port%';
                $cacheDef = new Definition($memcacheClass);
                $memcacheInstance = new Definition($memcacheInstanceClass);
                $memcacheInstance->addMethodCall('connect', array(
                    $memcacheHost, $memcachePort
                ));
                $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_memcache_instance', $documentManager['name']), $memcacheInstance);
                $cacheDef->addMethodCall('setMemcache', array(new Reference(sprintf('doctrine_phpcr.odm.%s_memcache_instance', $documentManager['name']))));
                break;
            case 'apc':
            case 'array':
            case 'xcache':
                $cacheDef = new Definition('%' . sprintf('doctrine_phpcr.odm.cache.%s.class', $cacheDriver['type']) . '%');
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" is an unrecognized Doctrine cache driver.', $cacheDriver['type']));
        }

        $cacheDef->setPublic(false);
        // generate a unique namespace for the given application
        $namespace = 'sf2phpcr_' . $documentManager['name'] . '_' . md5($container->getParameter('kernel.root_dir'));
        $cacheDef->addMethodCall('setNamespace', array($namespace));

        return $cacheDef;
    }

    protected function getObjectManagerElementName($name)
    {
        return 'doctrine_phpcr.odm.'.$name;
    }

    protected function getMappingObjectDefaultName()
    {
        return 'Document';
    }

    protected function getMappingResourceConfigDirectory()
    {
        return 'Resources/config/doctrine';
    }

    protected function getMappingResourceExtension()
    {
        return 'phpcr';
    }
}
