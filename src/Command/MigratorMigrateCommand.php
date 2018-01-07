<?php

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

        $id = $migrators[$migratorName] ?? null;
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
