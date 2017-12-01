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

namespace Doctrine\Bundle\PHPCRBundle\CacheWarmer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\Tools\Helper\UniqueNodeTypeHelper;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Hook the verification of uniquely mapped node types into the cache
 * warming process, thereby providing a useful indication to the
 * developer that something is wrong.
 */
class UniqueNodeTypeCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry A ManagerRegistry instance
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * This cache warmer is optional as it is just for error
     * checking and reporting back to the user.
     *
     * @return true
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $helper = new UniqueNodeTypeHelper();

        foreach ($this->registry->getManagers() as $documentManager) {
            $helper->checkNodeTypeMappings($documentManager);
        }
    }
}
