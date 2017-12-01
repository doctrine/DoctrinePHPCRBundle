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

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry as BaseManagerRegistry;
use Doctrine\ODM\PHPCR\PHPCRException;

/**
 * Symfony aware manager registry.
 *
 * @internal this class is intended to be used as service, but not for code reuse
 */
class ManagerRegistry extends BaseManagerRegistry
{
    public function __construct(
        ContainerInterface $container,
        array $connections,
        array $entityManagers,
        $defaultConnectionName,
        $defaultEntityManagerName,
        $proxyInterfaceName
    ) {
        $parentTraits = class_uses(parent::class);
        if (isset($parentTraits[ContainerAwareTrait::class])) {
            // this case should be removed when Symfony 3.4 becomes the lowest supported version
            // and then also, the constructor should type-hint Psr\Container\ContainerInterface
            $this->setContainer($container);
        } else {
            $this->container = $container;
        }

        parent::__construct(
            'PHPCR',
            $connections,
            $entityManagers,
            $defaultConnectionName,
            $defaultEntityManagerName,
            $proxyInterfaceName
        );
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * @param string $alias
     *
     * @return string
     *
     * @throws PHPCRException
     */
    public function getAliasNamespace($alias)
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getDocumentNamespace($alias);
            } catch (PHPCRException $e) {
            }
        }

        throw PHPCRException::unknownDocumentNamespace($alias);
    }

    /**
     * Get the admin connection associated to the connection.
     *
     * @param null $name
     *
     * @return object
     */
    public function getAdminConnection($name = null)
    {
        if (null === $name) {
            $name = $this->getDefaultConnectionName();
        }

        $serviceName = sprintf('doctrine_phpcr.admin.%s_session', $name);

        $connections = $this->getConnectionNames();
        if (!isset($connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->getName(), $name));
        }

        return $this->getService($serviceName);
    }
}
