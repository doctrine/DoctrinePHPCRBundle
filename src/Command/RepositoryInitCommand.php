<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\ODM\PHPCR\Tools\Console\Command\RegisterSystemNodeTypesCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
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
        if (class_exists(RegisterSystemNodeTypesCommand::class)) {
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

        $initializerManager->initialize($input->getOption('session'));
    }
}
