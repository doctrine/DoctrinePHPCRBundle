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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterEventListenersAndSubscribersPass;

use Doctrine\Bundle\PHPCRBundle\OptionalCommand\InitDoctrineDbalCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\JackrabbitCommand;

class DoctrinePHPCRBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterEventListenersAndSubscribersPass('doctrine.odm.phpcr.sessions', 'doctrine.odm.phpcr.%s_session.event_manager', 'doctrine.odm.phpcr'), PassConfig::TYPE_BEFORE_OPTIMIZATION);
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
}
