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

namespace Doctrine\Bundle\PHPCRBundle\DataFixtures;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor as BasePHPCRExecutor;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PHPCRExecutor extends BasePHPCRExecutor
{
    protected $initializerManager;

    /**
     * Construct new fixtures loader instance.
     *
     * @param DocumentManager $dm DocumentManager instance used for persistence.
     */
    public function __construct(
        DocumentManager $dm,
        PHPCRPurger $purger = null,
        InitializerManager $initializerManager = null
    ) {
        parent::__construct($dm, $purger);

        $this->initializerManager = $initializerManager;
    }

    public function purge()
    {
        parent::purge();

        if ($this->initializerManager) {
            $this->initializerManager->setLoggingClosure($this->logger);
            $this->initializerManager->initialize();
        }
    }
}
