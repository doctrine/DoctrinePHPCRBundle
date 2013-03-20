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

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

use Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension;

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
    /** @var XmlFileLoader */
    private $loader;

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);
        $this->loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (isset($config['workspace_dir'])) {
            $container->setParameter('doctrine_phpcr.workspace_dir', $config['workspace_dir']);
        }

        if (isset($config['jackrabbit_jar'])) {
            $container->setParameter('doctrine_phpcr.jackrabbit_jar', $config['jackrabbit_jar']);
        }

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
        $this->loader->load('phpcr.xml');

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
                        $this->loader->load('jackalope.xml');
                        $loaded['jackalope'] = true;
                    }
                    $this->loadJackalopeSession($session, $container, $type);
                    break;
                case 'midgard2':
                    if (empty($loaded['midgard2'])) {
                        $this->loader->load('midgard2.xml');
                        $loaded['midgard2'] = true;
                    }
                    $this->loadMidgard2Session($session, $container);
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
                if (isset($session['backend']['caches'])) {
                    foreach ($session['backend']['caches'] as $key => $cache) {
                        $parameters['jackalope.data_caches'][$key] = new Reference($cache);
                    }
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
                // Factory
                if (isset($session['backend']['factory'])) {
                    /**
                     * If it is a class, pass the name as is, else assume it is
                     * a service id and get a reference to it
                     */
                    if (class_exists($session['backend']['factory'])) {
                        $parameters['jackalope.factory'] = $session['backend']['factory'];
                    } else {
                        $parameters['jackalope.factory'] = new Reference($session['backend']['factory']);
                    }
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

        $definition = $container
            ->setDefinition($session['service_name'], new DefinitionDecorator('doctrine_phpcr.jackalope.session'))
            ->setFactoryService(sprintf('doctrine_phpcr.jackalope.repository.%s', $session['name']))
            ->replaceArgument(0, new Reference(sprintf('doctrine_phpcr.%s_credentials', $session['name'])))
            ->replaceArgument(1, $session['workspace'])
        ;

        if (isset($session['options']) && is_array($session['options'])) {
            foreach ($session['options'] as $key => $value) {
                $definition->addMethodCall('setSessionOption', array($key, $value));
            }
        }
    }

    private function loadMidgard2Session(array $session, ContainerBuilder $container)
    {
        $parameters = array();
        if (isset($session['backend']['config'])) {
            // Starting repository with a Midgard2 INI file
            $parameters['midgard2.configuration.file'] = $session['backend']['config'];
        } else if (isset($session['backend']['db_name'])) {
            // Manually configured Midgard2 session
            foreach ($session['backend'] as $key => $value) {
                if (substr($key, 0, 3) !== 'db_') {
                    continue;
                }
                $dbkey = substr($key, 3);
                $parameters["midgard2.configuration.db.{$dbkey}"] = $value;
            }

            if (isset($session['backend']['blobdir'])) {
                $parameters['midgard2.configuration.blobdir'] = $session['backend']['blobdir'];
            }
            if (isset($session['backend']['loglevel'])) {
                $parameters['midgard2.configuration.loglevel'] = $session['backend']['loglevel'];
            }
        } else {
            throw new \InvalidArgumentException("You set an invalid Midgard2 PHPCR configuration for session '{$session['name']}'. Please provide a 'config' or 'db_name' key");
        }

        $factory = $container
            ->setDefinition('doctrine_phpcr.midgard2.repository', new DefinitionDecorator('doctrine_phpcr.midgard2.repository.factory'))
        ;
        $factory->replaceArgument(0, $parameters);

        $container
            ->setDefinition(sprintf('doctrine_phpcr.%s_credentials', $session['name']), new DefinitionDecorator('doctrine_phpcr.credentials'))
            ->replaceArgument(0, $session['username'])
            ->replaceArgument(1, $session['password'])
        ;

        $container
            ->setDefinition($session['service_name'], new DefinitionDecorator('doctrine_phpcr.midgard2.session'))
            ->replaceArgument(0, new Reference(sprintf('doctrine_phpcr.%s_credentials', $session['name'])))
            ->replaceArgument(1, $session['workspace'])
        ;
    }

    private function odmLoad(array $config, ContainerBuilder $container)
    {
        $this->loader->load('odm.xml');

        if (!empty($config['locales'])) {
            $this->loader->load('odm_multilang.xml');

            $container->setParameter('doctrine_phpcr.odm.locales', $config['locales']);
            $container->setParameter('doctrine_phpcr.odm.default_locale', key($config['locales']));

            $dm = $container->getDefinition('doctrine_phpcr.odm.document_manager.abstract');
            $dm->addMethodCall('setLocaleChooserStrategy', array(new Reference('doctrine_phpcr.odm.locale_chooser')));
        }

        $filter = isset($config['image']['imagine_filter'])
            ? $config['image']['imagine_filter']
            : false;
        $filters = isset($config['image']['extra_filters']) && is_array($config['image']['extra_filters'])
            ? array_merge(array($filter), $config['image']['extra_filters'])
            : array();
        $container->setParameter('doctrine_phpcr.odm.subscriber.imagine_cache.filter', $filter);
        $container->setParameter('doctrine_phpcr.odm.subscriber.imagine_cache.all_filters', $filters);
        if ($filter) {
            $this->loader->load('odm_imagine.xml');
        }
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('doctrine_phpcr.odm.subscriber.image_cache');
        }

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
    }

    private function loadOdmDocumentManager(array $documentManager, ContainerBuilder $container)
    {
        $odmConfigDefTemplate = empty($documentManager['configuration_id']) ? 'doctrine_phpcr.odm.configuration' : $documentManager['configuration_id'];
        $odmConfigDef = $container->setDefinition(sprintf('doctrine_phpcr.odm.%s_configuration', $documentManager['name']), new DefinitionDecorator($odmConfigDefTemplate));

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

        $container->setDefinition(
            sprintf(
                'doctrine_phpcr.odm.%s_session.event_manager',
                $documentManager['name']
            ),
            new DefinitionDecorator('doctrine_phpcr.odm.document_manager.event_manager')
        );

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
        $this->loadObjectManagerCacheDriver($documentManager, $container, 'metadata_cache');
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
