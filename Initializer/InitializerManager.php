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

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use PHPCR\SessionInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\Bundle\PHPCRBundle\Initializer\PhpcrInitializerInterface;
use Doctrine\Bundle\PHPCRBundle\Initializer\PhpcrOdmInitializerInterface;

/**
 * Service which is used to aggregate and execute the initializers.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class InitializerManager
{
    /**
     * @var InitializerInterface[]
     */
    protected $initializers = array();

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var \Closure
     */
    protected $loggingClosure = null;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Set a logging closure for use, for example, from a
     * console command.
     *
     * @param \Closure $closure
     */
    public function setLoggingClosure(\Closure $closure = null)
    {
        $this->loggingClosure = $closure;
    }

    /**
     * Add an initializer
     *
     * @param InitializerInterface $initializer
     */
    public function addInitializer(InitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;
    }

    /**
     * Iterate over the registered initializers and execute each of them.
     */
    public function initialize()
    {
        $loggingClosure = $this->loggingClosure;

        foreach ($this->initializers as $initializer) {
            if ($loggingClosure) {
                $loggingClosure(sprintf('<info>Executing initializer:</info> %s', $initializer->getName()));
            }

            $initializer->init($this->registry);
        }
    }
}
