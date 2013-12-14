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

namespace Doctrine\Bundle\PHPCRBundle\DataCollector;

use Symfony\Component\Stopwatch\Stopwatch;
use Jackalope\Transport\Logging\LoggerInterface;

/**
 * Logger.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class StopWatchLogger implements LoggerInterface
{
    protected $stopwatch;

    /**
     * Constructor.
     *
     * @param Stopwatch $stopwatch A Stopwatch instance
     */
    public function __construct(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function startCall($method, array $params = null)
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->start('doctrine_phpcr', 'doctrine_phpcr');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopCall()
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->stop('doctrine_phpcr');
        }
    }
}
