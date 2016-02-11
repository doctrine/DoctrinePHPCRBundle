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

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\ODM\PHPCR\Tools\Console\Command\RegisterSystemNodeTypesCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Command to collect init operations from any interested bundles. If phpcr-odm
 * is present also executes RegisterSystemNodeTypesCommand.
 */
class RepositoryInitCommand extends ContainerAwareCommand
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:repository:init')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command')
            ->setDescription('Initialize the PHPCR repository.')
            ->setHelp(<<<'EOT'
Run all initializers tagged with doctrine_phpcr.initializer to create documents
or base paths so the application can work. If phpcr-odm is present, also runs
the doctrine:phpcr:register-system-node-types command.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (class_exists('Doctrine\ODM\PHPCR\Tools\Console\Command\RegisterSystemNodeTypesCommand')) {
            DoctrineCommandHelper::setApplicationPHPCRSession(
                $this->getApplication(),
                $input->getOption('session')
            );

            $command = new RegisterSystemNodeTypesCommand();
            $command->setApplication($this->getApplication());
            $command->execute($input, $output);
        }

        $initializerManager = $this->getContainer()->get('doctrine_phpcr.initializer_manager');
        $initializerManager->setLoggingClosure(function ($message) use ($output) {
            $output->writeln($message);
        });

        $initializerManager->initialize();
    }
}
