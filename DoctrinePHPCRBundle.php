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

namespace Doctrine\Bundle\PHPCRBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterEventListenersAndSubscribersPass;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\InitDoctrineDbalCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\JackrabbitCommand;

class DoctrinePHPCRBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterEventListenersAndSubscribersPass('doctrine_phpcr.sessions', 'doctrine_phpcr.odm.%s_session.event_manager', 'doctrine_phpcr'), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }

    /**
     * @inheritDoc
     */
    public function registerCommands(Application $application)
    {
        parent::registerCommands($application);

        if (class_exists('\Jackalope\Tools\Console\Command\JackrabbitCommand')) {
            $application->add(new JackrabbitCommand());
        }
        if (class_exists('\Jackalope\Tools\Console\Command\InitDoctrineDbalCommand')) {
            $application->add(new InitDoctrineDbalCommand());
        }
    }

    public function boot()
    {
        // Register an autoloader for proxies to avoid issues when unserializing them when the ODM is used.
        if ($this->container->hasParameter('doctrine_phpcr.odm.proxy_namespace')) {
            $namespace = $this->container->getParameter('doctrine_phpcr.odm.proxy_namespace');
            $dir = $this->container->getParameter('doctrine_phpcr.odm.proxy_dir');
            // See https://github.com/symfony/symfony/pull/3419 for usage of references
            $container =& $this->container;

            $this->autoloader = function($class) use ($namespace, $dir, &$container) {
                if (0 === strpos($class, $namespace)) {
                    $fileName = str_replace('\\', '', substr($class, strlen($namespace) +1));
                    $file = $dir.DIRECTORY_SEPARATOR.$fileName.'.php';

                    if (!is_file($file) && $container->getParameter('kernel.debug')) {
                        $originalClassName = ClassUtils::getRealClass($class);
                        $registry = $container->get('doctrine_phpcr');

                        // Tries to auto-generate the proxy file
                        foreach ($registry->getManagers() as $dm) {

                            if ($dm->getConfiguration()->getAutoGenerateProxyClasses()) {
                                $classes = $dm->getMetadataFactory()->getAllMetadata();

                                foreach ($classes as $classMetadata) {
                                    if ($classMetadata->name == $originalClassName) {
                                        $dm->getProxyFactory()->generateProxyClasses(array($classMetadata));
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

    public function shutdown()
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }
}
