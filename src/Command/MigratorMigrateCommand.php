<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\Bundle\PHPCRBundle\Migrator\MigratorInterface;
use PHPCR\Util\Console\Command\BaseCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MigratorMigrateCommand extends BaseCommand
{
    use ContainerAwareTrait;

    protected function configure(): void
    {
        $this
            ->setName('doctrine:phpcr:migrator:migrate')
            ->setDescription('Migrates PHPCR data.')
            ->addArgument('migrator_name', InputArgument::OPTIONAL, 'The name of the alias/service to be used to migrate the data.')
            ->addOption('identifier', null, InputOption::VALUE_REQUIRED, 'Path or UUID of the node to migrate', '/')
            ->addOption('depth', null, InputOption::VALUE_REQUIRED, 'Set to a number to limit how deep into the tree to recurse', '-1')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
            ->setHelp(<<<'EOT'
To find the available 'migrators' run this command without an input argument
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new \InvalidArgumentException('Expected to find '.Application::class.' but got '.
                ($application ? \get_class($application) : null));
        }
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $application,
            $input->getOption('session')
        );
        $session = $this->getPhpcrSession();

        $migrators = $this->container->getParameter('doctrine_phpcr.migrate.migrators');

        $migratorName = $input->getArgument('migrator_name');
        if (!$migratorName) {
            $output->write('Available migrators:', true);
            $output->write(implode("\n", array_keys($migrators)), true);

            return 0;
        }

        $id = $migrators[$migratorName] ?? null;
        if (!$id || !$this->container->has($id)) {
            throw new \InvalidArgumentException("Wrong value '$migratorName' for migrator_name argument.\nAvailable migrators:\n".implode("\n", array_keys($migrators)));
        }

        $migrator = $this->container->get($id);
        if (!$migrator instanceof MigratorInterface) {
            throw new \InvalidArgumentException('Looked for a '.MigratorInterface::class.' but found '.($migrator ? \get_class($migrator) : $migrator));
        }
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
