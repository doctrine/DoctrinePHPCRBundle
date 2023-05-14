<?php

namespace Doctrine\Bundle\PHPCRBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\InitializerPass;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\MigratorPass;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\InitDoctrineDbalCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope\JackrabbitCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM\DocumentConvertTranslationCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM\DocumentMigrateClassCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM\InfoDoctrineCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM\VerifyUniqueNodeTypesMappingCommand;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\Version;
use Jackalope\Session;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand as BaseInitDoctrineDbalCommand;
use Jackalope\Tools\Console\Command\JackrabbitCommand as BaseJackrabbitCommand;
use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterEventListenersAndSubscribersPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrinePHPCRBundle extends Bundle
{
    /**
     * Autoloader for proxies.
     */
    private ?\Closure $autoloader;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new MigratorPass());
        $container->addCompilerPass(new InitializerPass());
        if (class_exists(Version::class)) {
            $container->addCompilerPass(new RegisterEventListenersAndSubscribersPass('doctrine_phpcr.sessions', 'doctrine_phpcr.%s_session.event_manager', 'doctrine_phpcr'), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        }
    }

    public function registerCommands(Application $application): void
    {
        parent::registerCommands($application);

        if (class_exists(Version::class)) {
            $application->add(new DocumentMigrateClassCommand());
            $application->add(new InfoDoctrineCommand());
            $application->add(new VerifyUniqueNodeTypesMappingCommand());
            $application->add(new DocumentConvertTranslationCommand());
        }

        if (class_exists(BaseJackrabbitCommand::class)) {
            $application->add(new JackrabbitCommand());
        }
        if (class_exists(BaseInitDoctrineDbalCommand::class)) {
            $application->add(new InitDoctrineDbalCommand());
        }
    }

    public function boot(): void
    {
        // Register an autoloader for proxies to avoid issues when unserializing them when the ODM is used.
        if ($this->container->hasParameter('doctrine_phpcr.odm.proxy_namespace')) {
            $namespace = $this->container->getParameter('doctrine_phpcr.odm.proxy_namespace');
            $dir = $this->container->getParameter('doctrine_phpcr.odm.proxy_dir');
            // See https://github.com/symfony/symfony/pull/3419 for usage of references
            $container = &$this->container;

            $this->autoloader = function ($class) use ($namespace, $dir, &$container) {
                if (0 === strpos($class, $namespace)) {
                    $fileName = str_replace('\\', '', substr($class, \strlen($namespace) + 1));
                    $file = $dir.\DIRECTORY_SEPARATOR.$fileName.'.php';

                    if (!is_file($file) && $container->getParameter('doctrine_phpcr.odm.auto_generate_proxy_classes')) {
                        $originalClassName = ClassUtils::getRealClass($class);
                        $registry = $container->get('doctrine_phpcr');

                        // Tries to auto-generate the proxy file
                        foreach ($registry->getManagers() as $dm) {
                            if ($dm->getConfiguration()->getAutoGenerateProxyClasses()) {
                                $classes = $dm->getMetadataFactory()->getAllMetadata();

                                foreach ($classes as $classMetadata) {
                                    if ($classMetadata->name === $originalClassName) {
                                        $dm->getProxyFactory()->generateProxyClasses([$classMetadata]);
                                    }
                                }
                            }
                        }

                        clearstatcache($file);
                    }

                    if (is_file($file)) {
                        require $file;
                    }
                }
            };
            spl_autoload_register($this->autoloader);
        }
    }

    public function shutdown(): void
    {
        if (isset($this->autoloader)) {
            spl_autoload_unregister($this->autoloader);
            unset($this->autoloader);
        }

        $this->clearDocumentManagers();
        $this->closeConnections();
    }

    /**
     * Clear all document managers to clear references to entities for GC.
     */
    private function clearDocumentManagers(): void
    {
        if (!$this->container->hasParameter('doctrine_phpcr.odm.document_managers')) {
            return;
        }

        foreach ($this->container->getParameter('doctrine_phpcr.odm.document_managers') as $id) {
            if ($this->container instanceof IntrospectableContainerInterface && !$this->container->initialized($id)) {
                continue;
            }

            $this->container->get($id)->clear();
        }
    }

    /**
     * Close all connections to avoid reaching too many connections in the process when booting again later (tests).
     */
    private function closeConnections(): void
    {
        if (!$this->container->hasParameter('doctrine_phpcr.sessions')) {
            return;
        }

        foreach ($this->container->getParameter('doctrine_phpcr.sessions') as $id) {
            if ($this->container instanceof IntrospectableContainerInterface && !$this->container->initialized($id)) {
                continue;
            }

            $session = $this->container->get($id);
            if (!$session instanceof Session) {
                return;
            }

            $session->getTransport()->logout();
        }
    }
}
