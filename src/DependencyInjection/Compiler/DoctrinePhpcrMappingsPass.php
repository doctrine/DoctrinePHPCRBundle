<?php

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler;

use Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\PHPCR\Mapping\Driver\AttributeDriver;
use Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver;
use Doctrine\ODM\PHPCR\Mapping\Driver\YamlDriver;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class for Symfony bundles to configure mappings for model classes not in the
 * automapped folder.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class DoctrinePhpcrMappingsPass extends RegisterMappingsPass
{
    /**
     * You should not directly instantiate this class but use one of the factory methods.
     *
     * @param Definition|Reference $driver            driver DI definition or reference
     * @param array                $namespaces        list of namespaces handled by $driver
     * @param string[]             $managerParameters List of container parameters that could hold
     *                                                the manager name.
     *                                                doctrine_phpcr.odm.default_document_manager
     *                                                is appended automatically.
     * @param string|bool          $enabledParameter  Service container parameter that must be
     *                                                present to enable the mapping. Set to false
     *                                                to not do any check, optional.
     * @param array                $aliasMap          map of alias to namespace
     */
    public function __construct($driver, array $namespaces, array $managerParameters, $enabledParameter = false, array $aliasMap = [])
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
     * @param array       $namespaces        Hashmap of directory path to namespace
     * @param string[]    $managerParameters List of parameters that could which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list.
     * @param string|bool $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional.
     * @param string[]    $aliasMap          map of alias to namespace
     */
    public static function createXmlMappingDriver(
        array $namespaces,
        array $managerParameters = [],
        $enabledParameter = false,
        array $aliasMap = []
    ): self {
        $arguments = [$namespaces, '.phpcr.xml'];
        $locator = new Definition(SymfonyFileLocator::class, $arguments);
        $driver = new Definition(XmlDriver::class, [$locator]);

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array       $namespaces        Hashmap of directory path to namespace
     * @param string[]    $managerParameters List of parameters that could which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list.
     * @param string|bool $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional.
     * @param string[]    $aliasMap          map of alias to namespace
     */
    public static function createYamlMappingDriver(
        array $namespaces,
        array $managerParameters = [],
        $enabledParameter = false,
        array $aliasMap = []
    ): self {
        $arguments = [$namespaces, '.phpcr.yml'];
        $locator = new Definition(SymfonyFileLocator::class, $arguments);
        $driver = new Definition(YamlDriver::class, [$locator]);

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array       $mappings          Hashmap of directory path to namespace
     * @param string[]    $managerParameters List of parameters that could which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list.
     * @param string|bool $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional.
     * @param string[]    $aliasMap          map of alias to namespace
     */
    public static function createPhpMappingDriver(
        array $mappings,
        array $managerParameters = [],
        $enabledParameter = false,
        array $aliasMap = []
    ): self {
        $arguments = [$mappings, '.php'];
        $locator = new Definition(SymfonyFileLocator::class, $arguments);
        $driver = new Definition(PHPDriver::class, [$locator]);

        return new self($driver, $mappings, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array       $namespaces        List of namespaces that are handled with annotation mapping
     * @param array       $directories       List of directories to look for annotated classes
     * @param string[]    $managerParameters List of parameters that could which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list.
     * @param string|bool $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional.
     * @param string[]    $aliasMap          map of alias to namespace
     */
    public static function createAnnotationMappingDriver(
        array $namespaces,
        array $directories,
        array $managerParameters = [],
        $enabledParameter = false,
        array $aliasMap = []
    ): self {
        $reader = new Reference('doctrine_phpcr.odm.metadata.annotation_reader');
        $driver = new Definition(AnnotationDriver::class, [$reader, $directories]);

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param string[]     $namespaces                List of namespaces that are handled with attribute mapping
     * @param string[]     $directories               List of directories to look for classes with attributes
     * @param string[]     $managerParameters         List of parameters that could which object manager name
     *                                                your bundle uses. This compiler pass will automatically
     *                                                append the parameter name for the default entity manager
     *                                                to this list.
     * @param string|false $enabledParameter          Service container parameter that must be present to
     *                                                enable the mapping. Set to false to not do any check,
     *                                                optional.
     * @param string[]     $aliasMap                  map of alias to namespace
     * @param bool         $reportFieldsWhereDeclared Will report fields for the classes where they are declared
     *
     * @return self
     */
    public static function createAttributeMappingDriver(array $namespaces, array $directories, array $managerParameters = [], $enabledParameter = false, array $aliasMap = [], bool $reportFieldsWhereDeclared = false)
    {
        $driver = new Definition(AttributeDriver::class, [$directories, $reportFieldsWhereDeclared]);

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }

    /**
     * @param array       $namespaces        List of namespaces that are handled with static php mapping
     * @param array       $directories       List of directories to look for static php mapping files
     * @param string[]    $managerParameters List of parameters that could which object manager name
     *                                       your bundle uses. This compiler pass will automatically
     *                                       append the parameter name for the default entity manager
     *                                       to this list.
     * @param string|bool $enabledParameter  Service container parameter that must be present to
     *                                       enable the mapping. Set to false to not do any check,
     *                                       optional.
     * @param string[]    $aliasMap          map of alias to namespace
     */
    public static function createStaticPhpMappingDriver(
        array $namespaces,
        array $directories,
        array $managerParameters = [],
        $enabledParameter = false,
        array $aliasMap = []
    ): self {
        $driver = new Definition(StaticPHPDriver::class, [$directories]);

        return new self($driver, $namespaces, $managerParameters, $enabledParameter, $aliasMap);
    }
}
