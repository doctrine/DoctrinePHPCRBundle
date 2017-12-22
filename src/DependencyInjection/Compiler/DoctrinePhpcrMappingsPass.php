<?php


namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class for Symfony bundles to configure mappings for model classes not in the
 * automapped folder.
 *
 * NOTE: alias is only supported by Symfony 2.6+ and will be ignored with older versions.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class DoctrinePhpcrMappingsPass extends RegisterMappingsPass
{
    /**
     * You should not directly instantiate this class but use one of the
     * factory methods.
     *
     * @param Definition|Reference $driver            driver DI definition or reference
     * @param array                $namespaces        list of namespaces handled by $driver
     * @param string[]             $managerParameters List of container parameters that could hold
     *                                                the manager name.
     *                                                doctrine_phpcr.odm.default_document_manager
     *                                                is appended automatically.
     * @param bool                 $enabledParameter  Service container parameter that must be
     *                                                present to enable the mapping. Set to false
     *                                                to not do any check, optional.
     * @param array                $aliasMap          map of alias to namespace
     */
    public function __construct($driver, array $namespaces, array $managerParameters, $enabledParameter = false, array $aliasMap = array())
    {
        $managerParameters[] = 'doctrine_phpcr.odm.default_document_manager';
        parent::__construct(
            $driver,
            $namespaces,
            $managerParameters,
            'doctrine_phpcr.odm.%s_metadata_driver',
            $enabledParameter,
            'doctrine_phpcr.odm.%s_configuration',
            'addDocumentNamespace',
            $aliasMap
        );
    }

    /**
     * @param array    $namespaces        Hashmap of directory path to namespace
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string   $enabledParameter  Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     * @param string[] $aliasMap          map of alias to namespace
     */
    public static function createXmlMappingDriver(array $namespaces, array $managerParameters = array(), $enabledParameter = false, array $aliasMap = array())
    {
        $arguments = array($namespaces, '.phpcr.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver', array($locator));

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array    $namespaces        Hashmap of directory path to namespace
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string   $enabledParameter  Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     * @param string[] $aliasMap          map of alias to namespace
     */
    public static function createYamlMappingDriver(array $namespaces, array $managerParameters = array(), $enabledParameter = false, array $aliasMap = array())
    {
        $arguments = array($namespaces, '.phpcr.yml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\YamlDriver', array($locator));

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array    $mappings          Hashmap of directory path to namespace
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string   $enabledParameter  Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     * @param string[] $aliasMap          map of alias to namespace
     */
    public static function createPhpMappingDriver(array $mappings, array $managerParameters = array(), $enabledParameter = false, array $aliasMap = array())
    {
        $arguments = array($mappings, '.php');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\Common\Persistence\Mapping\Driver\PHPDriver', array($locator));

        return new self($driver, $mappings, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array    $namespaces        List of namespaces that are handled with annotation mapping
     * @param array    $directories       List of directories to look for annotated classes
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string   $enabledParameter  Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     * @param string[] $aliasMap          map of alias to namespace
     */
    public static function createAnnotationMappingDriver(array $namespaces, array $directories, array $managerParameters = array(), $enabledParameter = false, array $aliasMap = array())
    {
        $reader = new Reference('doctrine_phpcr.odm.metadata.annotation_reader');
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver', array($reader, $directories));

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array    $namespaces        List of namespaces that are handled with static php mapping
     * @param array    $directories       List of directories to look for static php mapping files
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string   $enabledParameter  Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     * @param string[] $aliasMap          map of alias to namespace
     */
    public static function createStaticPhpMappingDriver(array $namespaces, array $directories, array $managerParameters = array(), $enabledParameter = false, array $aliasMap = array())
    {
        $driver = new Definition('Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver', array($directories));

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }
}
