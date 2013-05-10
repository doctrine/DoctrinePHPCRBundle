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

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler;

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
     * You should not directly instantiate this class but use one of the
     * factory methods.
     *
     * @param Definition|Reference $driver            the driver to use
     * @param array                $namespaces        list of namespaces this driver should handle.
     * @param string[]             $managerParameters ordered list of container parameters that may
     *      provide the name of the manager to register the mappings for. The first non-empty name
     *      is used, the others skipped.
     * @param bool                 $enabledParameter  if specified, the compiler pass only executes
     *      if this parameter exists in the service container.
     */
    public function __construct($driver, $namespaces, array $managerParameters, $enabledParameter = false)
    {
        $managerParameters[] = 'doctrine_phpcr.odm.default_document_manager';
        parent::__construct(
            $driver,
            $namespaces,
            $managerParameters,
            'doctrine_phpcr.odm.%s_metadata_driver',
            $enabledParameter
        );

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
     */
    public static function createXmlMappingDriver(array $mappings, array $managerParameters = array(), $enabledParameter = false)
    {
        $arguments = array($mappings, '.phpcr.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver', array($locator));

        return new DoctrinePhpcrMappingsPass($driver, $mappings, $managerParameters, $enabledParameter);
    }

    /**
     * @param array  $mappings            Hashmap of directory path to namespace
     * @param string[] $managerParameters List of parameters that could which object manager name
     *                                    your bundle uses. This compiler pass will automatically
     *                                    append the parameter name for the default entity manager
     *                                    to this list.
     * @param string $enabledParameter    Service container parameter that must be present to
     *                                    enable the mapping. Set to false to not do any check,
     *                                    optional.
     */
    public static function createYamlMappingDriver(array $mappings, array $managerParameters = array(), $enabledParameter = false)
    {
        $arguments = array($mappings, '.phpcr.yml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\YamlDriver', array($locator));

        return new DoctrinePhpcrMappingsPass($driver, $mappings, $managerParameters, $enabledParameter);
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
     */
    public static function createPhpMappingDriver(array $mappings, array $managerParameters = array(), $enabledParameter = false)
    {
        $arguments = array($mappings, '.php');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\Common\Persistence\Mapping\Driver\PHPDriver', array($locator));

        return new DoctrinePhpcrMappingsPass($driver, $mappings, $managerParameters, $enabledParameter);
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
     */
    public static function createAnnotationMappingDriver(array $namespaces, array $directories, array $managerParameters = array(), $enabledParameter = false)
    {
        $reader = new Reference('doctrine_phpcr.odm.metadata.annotation_reader');
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\AnnotationDriver', array($reader, $directories));

        return new DoctrinePhpcrMappingsPass($driver, $namespaces, $managerParameters, $enabledParameter);
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
     */
    public static function createStaticPhpMappingDriver(array $namespaces, array $directories, array $managerParameters = array(), $enabledParameter = false)
    {
        $driver = new Definition('Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver', array($directories));

        return new DoctrinePhpcrMappingsPass($driver, $namespaces, $managerParameters, $enabledParameter);
    }
}
