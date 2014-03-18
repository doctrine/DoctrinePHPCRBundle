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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the PHPCR extension
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('doctrine_phpcr')
            ->children()
                ->scalarNode('jackrabbit_jar')->end()
                ->scalarNode('workspace_dir')->end()
                ->scalarNode('dump_max_line_length')->defaultValue(120)->end()
            ->end()
        ;

        $this->addPHPCRSection($rootNode);
        $this->addOdmSection($rootNode);

        return $treeBuilder;
    }

    private function addPHPCRSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->arrayNode('session')
                ->beforeNormalization()
                    ->ifTrue(function ($v) { return !is_array($v) || (is_array($v) && !array_key_exists('sessions', $v) && !array_key_exists('session', $v)); })
                    ->then(function ($v) {
                        if (!is_array($v)) {
                            $v = array();
                        }

                        $session = array();
                        foreach (array(
                            'workspace',
                            'username',
                            'password',
                            'backend',
                            'options'
                        ) as $key) {
                            if (array_key_exists($key, $v)) {
                                $session[$key] = $v[$key];
                                unset($v[$key]);
                            }
                        }
                        $v['default_session'] = isset($v['default_session']) ? (string) $v['default_session'] : 'default';
                        $v['sessions'] = array($v['default_session'] => $session);

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

    private function getPHPCRSessionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sessions');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->fixXmlConfig('option')
                    ->scalarNode('workspace')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('username')->defaultNull()->end()
                    ->scalarNode('password')->defaultNull()->end()
                    ->arrayNode('backend')
                        ->useAttributeAsKey('name')
                        ->fixXmlConfig('parameter')
                        ->arrayNode('parameters')
                            ->useAttributeAsKey('key')
                            ->prototype('scalar')->end()
                        ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('options')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function addOdmSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('odm')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return null === $v || (is_array($v) && !array_key_exists('document_managers', $v) && !array_key_exists('document_manager', $v)); })
                        ->then(function ($v) {
                            $v = (array) $v;
                            // Key that should not be rewritten to the connection config
                            $excludedKeys = array(
                                'default_document_manager' => true,
                                'auto_generate_proxy_classes' => true,
                                'proxy_dir' => true,
                                'proxy_namespace' => true,
                                'locale_fallback' => true,
                                'locales' => true,
                            );
                            $documentManagers = array();
                            foreach ($v as $key => $value) {
                                if (isset($excludedKeys[$key])) {
                                    continue;
                                }
                                $documentManagers[$key] = $v[$key];
                                unset($v[$key]);
                            }
                            $v['default_document_manager'] = isset($v['default_document_manager']) ? (string) $v['default_document_manager'] : 'default';
                            $v['document_managers'] = array($v['default_document_manager'] => $documentManagers);

                            return $v;
                        })
                    ->end()
                    ->children()
                        ->scalarNode('default_document_manager')->end()
                        ->booleanNode('auto_generate_proxy_classes')->defaultFalse()->end()
                        ->scalarNode('proxy_dir')->defaultValue('%kernel.cache_dir%/doctrine/PHPCRProxies')->end()
                        ->scalarNode('proxy_namespace')->defaultValue('PHPCRProxies')->end()
                        ->enumNode('locale_fallback')
                            ->values(array('hardcoded', 'merge', 'replace'))
                            ->defaultValue('hardcoded')
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

    private function getOdmLocaleNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('locales');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->prototype('scalar')
            ->end()
        ->end()
        ;

        return $node;
    }

    private function getOdmDocumentManagersNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('document_managers');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->append($this->getOdmCacheDriverNode('metadata_cache_driver'))
                ->children()
                    ->scalarNode('session')->end()
                    ->scalarNode('configuration_id')->end()
                    ->scalarNode('class_metadata_factory_name')->defaultValue('Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory')->end()
                    ->scalarNode('auto_mapping')->defaultFalse()->end()
                    ->scalarNode('default_repository_class')->defaultValue('Doctrine\ODM\PHPCR\DocumentRepository')->end()
                    ->scalarNode('repository_factory')->defaultNull()->end()
                ->end()
                ->fixXmlConfig('mapping')
                ->children()
                    ->arrayNode('mappings')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v) { return array('type' => $v); })
                            ->end()
                            ->treatNullLike(array())
                            ->treatFalseLike(array('mapping' => false))
                            ->performNoDeepMerging()
                            ->children()
                                ->scalarNode('mapping')->defaultValue(true)->end()
                                ->scalarNode('type')->end()
                                ->scalarNode('dir')->end()
                                ->scalarNode('alias')->end()
                                ->scalarNode('prefix')->end()
                                ->booleanNode('is_bundle')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getOdmCacheDriverNode($name)
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root($name);

        $node
            ->addDefaultsIfNotSet()
            ->beforeNormalization()
            ->ifString()
            ->then(function($v)
        {
            return array('type' => $v);
        })
            ->end()
            ->children()
            ->scalarNode('type')->defaultValue('array')->end()
            ->scalarNode('host')->end()
            ->scalarNode('port')->end()
            ->scalarNode('instance_class')->end()
            ->scalarNode('class')->end()
            ->scalarNode('id')->end()
            ->end();

        return $node;
    }
}
