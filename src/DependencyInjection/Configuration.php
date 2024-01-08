<?php

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\EventListener\LocaleListener;
use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory;
use Doctrine\ODM\PHPCR\Translation\Translation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Configuration for the PHPCR extension.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('doctrine_phpcr');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('jackrabbit_jar')->end()
                ->scalarNode('workspace_dir')->end()
                ->scalarNode('dump_max_line_length')->defaultValue(120)->end()
                ->scalarNode('manager_registry_service_id')->defaultNull()->end()
            ->end()
        ;

        $this->addPHPCRSection($root);
        $this->addOdmSection($root);

        return $treeBuilder;
    }

    private function addPHPCRSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
            ->arrayNode('session')
                ->beforeNormalization()
                    ->ifTrue(function ($v) {
                        return !\is_array($v) || (\is_array($v) && !\array_key_exists('sessions', $v) && !\array_key_exists('session', $v));
                    })
                    ->then(function ($v) {
                        if (!\is_array($v)) {
                            $v = [];
                        }

                        $session = [];
                        foreach ([
                            'workspace',
                            'username',
                            'password',
                            'admin_username',
                            'admin_password',
                            'backend',
                            'options',
                        ] as $key) {
                            if (\array_key_exists($key, $v)) {
                                $session[$key] = $v[$key];
                                unset($v[$key]);
                            }
                        }
                        $v['default_session'] = (string) ($v['default_session'] ?? 'default');
                        $v['sessions'] = [$v['default_session'] => $session];

                        return $v;
                    })
                ->end()
                ->children()
                    ->scalarNode('default_session')->end()
                ->end()
                ->fixXmlConfig('session')
                ->append($this->getPHPCRSessionsNode())
            ->end()
        ;
    }

    private function getPHPCRSessionsNode(): NodeDefinition
    {
        $root = (new TreeBuilder('sessions'))->getRootNode();

        $root
            ->requiresAtLeastOneElement()
            ->fixXmlConfig('option')
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('workspace')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('username')->defaultNull()->end()
                    ->scalarNode('password')->defaultNull()->end()
                    ->scalarNode('admin_username')->defaultNull()->end()
                    ->scalarNode('admin_password')->defaultNull()->end()
                    ->arrayNode('backend')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifArray()
                            ->then(function ($v) {
                                $map = [
                                    'check_login_on_server' => 'jackalope.check_login_on_server',
                                    'disable_stream_wrapper' => 'jackalope.disable_stream_wrapper',
                                    'disable_transactions' => 'jackalope.disable_transactions',
                                ];
                                foreach ($map as $key => $jackalope) {
                                    if (isset($v[$key])) {
                                        $v['parameters'][$jackalope] = $v[$key];
                                        unset($v[$key]);
                                    }
                                }

                                return $v;
                            })
                        ->end()
                        ->validate()
                            ->always()
                            ->then(function ($v) {
                                switch ($v['type']) {
                                    case 'prismic':
                                        if (!isset($v['url'])) {
                                            throw new InvalidConfigurationException('prismic backend requires the url argument.');
                                        }

                                        break;
                                    case 'jackrabbit':
                                        if (!isset($v['url'])) {
                                            throw new InvalidConfigurationException('jackrabbit backend requires the url argument.');
                                        }

                                        break;
                                    case 'doctrinedbal':
                                        break;
                                }

                                return $v;
                            })
                        ->end()
                        ->fixXmlConfig('parameter')
                        ->children()
                            ->enumNode('type')
                                ->values(['jackrabbit', 'doctrinedbal', 'prismic'])
                                ->defaultValue('jackrabbit')
                            ->end()
                            // all jackalope
                            ->scalarNode('factory')->defaultNull()->end()
                            ->booleanNode('logging')->defaultFalse()->end()
                            ->booleanNode('profiling')->defaultFalse()->end()
                            ->booleanNode('backtrace')->defaultFalse()->end()
                            ->arrayNode('parameters')
                                ->useAttributeAsKey('key')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('curl_options')
                                ->useAttributeAsKey('key')
                                ->prototype('scalar')->end()
                            ->end()

                            // jackrabbit
                            ->scalarNode('url')->end()
                            // doctrinedbal
                            ->scalarNode('connection')->end()
                            ->arrayNode('caches')
                                ->children()
                                    ->scalarNode('meta')
                                        ->info('For jackalope 2.x, this must be a PSR-16 simple cache instance. Jackalope 1.x also accepts a Doctrine Cache')
                                    ->end()
                                    ->scalarNode('nodes')
                                        ->info('For jackalope 2.x, this must be a PSR-16 simple cache instance. Jackalope 1.x also accepts a Doctrine Cache')
                                    ->end()
                                    ->scalarNode('query')
                                        ->info('For jackalope 2.x, this must be a PSR-16 simple cache instance. Jackalope 1.x also accepts a Doctrine Cache')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('options')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $root;
    }

    private function addOdmSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('odm')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return null === $v || (\is_array($v) && !\array_key_exists('document_managers', $v) && !\array_key_exists('document_manager', $v));
                        })
                        ->then(function ($v) {
                            $v = (array) $v;
                            // Key that should not be rewritten to the connection config
                            $excludedKeys = [
                                'default_document_manager' => true,
                                'auto_generate_proxy_classes' => true,
                                'proxy_dir' => true,
                                'proxy_namespace' => true,
                                'locale_fallback' => true,
                                'locales' => true,
                                'locale' => true,
                                'locale_chooser' => true,
                                'default_locale' => true,
                            ];
                            $documentManagers = [];
                            foreach ($v as $key => $value) {
                                if (isset($excludedKeys[$key])) {
                                    continue;
                                }
                                $documentManagers[$key] = $v[$key];
                                unset($v[$key]);
                            }
                            $v['default_document_manager'] = (string) ($v['default_document_manager'] ?? 'default');
                            $v['document_managers'] = [$v['default_document_manager'] => $documentManagers];

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->scalarNode('default_document_manager')->end()
                        ->booleanNode('auto_generate_proxy_classes')->defaultFalse()->end()
                        ->scalarNode('proxy_dir')->defaultValue('%kernel.cache_dir%/doctrine/PHPCRProxies')->end()
                        ->scalarNode('proxy_namespace')->defaultValue('PHPCRProxies')->end()
                        ->scalarNode('locale_chooser')
                            ->info('Specify custom locale chooser service ID')
                            ->defaultNull()
                        ->end()
                        ->enumNode('locale_fallback')
                            ->values([LocaleListener::FALLBACK_HARDCODED, LocaleListener::FALLBACK_MERGE, LocaleListener::FALLBACK_REPLACE])
                            ->defaultValue(LocaleListener::FALLBACK_MERGE)
                        ->end()
                        ->scalarNode('default_locale')->end()
                        ->arrayNode('namespaces')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('alias')
                                            ->defaultValue(class_exists(Translation::class) ? Translation::LOCALE_NAMESPACE : null)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->fixXmlConfig('document_manager')
                    ->append($this->getOdmDocumentManagersNode())
                    ->fixXmlConfig('locale')
                    ->append($this->getOdmLocaleNode())
                ->end()
            ->end()
        ;
    }

    private function getOdmLocaleNode(): NodeDefinition
    {
        $root = (new TreeBuilder('locales'))->getRootNode();

        $root
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(function ($v) {
                        return isset($v['fallback']);
                    })
                    ->then(function ($v) {
                        $fallbackLocales = [];
                        foreach ($v['fallback'] as $fallback) {
                            $fallbackLocales[] = $fallback;
                        }

                        return $fallbackLocales;
                    })
                ->end()
                ->prototype('scalar')
            ->end()
        ->end()
        ;

        return $root;
    }

    private function getOdmDocumentManagersNode(): NodeDefinition
    {
        $root = (new TreeBuilder('document_managers'))->getRootNode();

        $root
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->append($this->getOdmCacheDriverNode('metadata_cache_driver'))
                ->children()
                    ->scalarNode('session')->end()
                    ->scalarNode('configuration_id')->end()
                    ->scalarNode('class_metadata_factory_name')->defaultValue(ClassMetadataFactory::class)->end()
                    ->scalarNode('auto_mapping')->defaultFalse()->end()
                    ->scalarNode('default_repository_class')->defaultValue(DocumentRepository::class)->end()
                    ->scalarNode('repository_factory')->defaultNull()->end()
                ->end()
                ->fixXmlConfig('mapping')
                ->children()
                    ->arrayNode('mappings')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['type' => $v];
                                })
                            ->end()
                            ->treatNullLike([])
                            ->treatFalseLike(['mapping' => false])
                            ->performNoDeepMerging()
                            ->children()
                                ->scalarNode('mapping')->defaultValue(true)->end()
                                ->scalarNode('type')->end()
                                ->scalarNode('dir')->end()
                                ->scalarNode('prefix')->end()
                                ->booleanNode('is_bundle')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $root;
    }

    private function getOdmCacheDriverNode($name): NodeDefinition
    {
        $root = (new TreeBuilder($name))->getRootNode();

        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('type')
                    ->values(['array', 'service'])
                    ->defaultValue('array')
                ->end()
                ->scalarNode('id')
                    ->info('This needs to be a PSR-6 cache service.')
                ->end()
            ->end();

        return $root;
    }
}
