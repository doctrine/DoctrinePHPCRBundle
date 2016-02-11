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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MigratorMigrateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:phpcr:migrator:migrate')
            ->setDescription('Migrates PHPCR data.')
            ->addArgument('migrator_name', InputArgument::OPTIONAL, 'The name of the alias/service to be used to migrate the data.')
            ->addOption('identifier', null, InputOption::VALUE_OPTIONAL, 'Path or UUID of the node to dump', '/')
            ->addOption('depth', null, InputOption::VALUE_OPTIONAL, 'Set to a number to limit how deep into the tree to recurse', '-1')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command')
            ->setHelp(<<<'EOT'
To find the available 'migrators' run this command without an input argument
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(),
            $input->getOption('session')
        );
        $session = $this->getHelperSet()->get('phpcr')->getSession();

        $container = $this->getContainer();
        $migrators = $container->getParameter('doctrine_phpcr.migrate.migrators');

        $migratorName = $input->getArgument('migrator_name');
        if (!$migratorName) {
            $output->write('Available migrators:', true);
            $output->write(implode("\n", array_keys($migrators)), true);

            return 0;
        }

        $id = isset($migrators[$migratorName]) ? $migrators[$migratorName] : null;
        if (!$id || !$container->has($id)) {
            throw new \InvalidArgumentException("Wrong value '$migratorName' for migrator_name argument.\nAvailable migrators:\n".implode("\n", array_keys($migrators)));
        }

        $migrator = $container->get($id);

        $migrator->init($session, $output);

        $identifier = $input->getOption('identifier');
        $depth = $input->getOption('depth');
        $output->write("Migrating identifier '$identifier' with depth '$depth' using '$migratorName'", true);
        $exitCode = $migrator->migrate($identifier, $depth);

        if (0 === $exitCode) {
            $output->write('Successful', true);
        } else {
            $output->write('Failed!', true);
        }

        return $exitCode;
    }
}
