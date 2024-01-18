<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\ODM\PHPCR\Tools\Console\Command\RegisterSystemNodeTypesCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to collect init operations from any interested bundles.
 *
 * If phpcr-odm is present, this also executes RegisterSystemNodeTypesCommand.
 */
class RepositoryInitCommand extends Command
{
    private const NAME = 'doctrine:phpcr:repository:init';

    public function __construct(
        private InitializerManager $initializerManager
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(self::NAME)
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
            ->setDescription('Initialize the PHPCR repository.')
            ->setHelp(<<<'EOT'
Run all initializers tagged with doctrine_phpcr.initializer to create documents
or base paths so the application can work. If phpcr-odm is present, also runs
the doctrine:phpcr:register-system-node-types command.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $this->initializerManager->setLoggingClosure(function ($message) use ($output) {
            $output->writeln($message);
        });

        $this->initializerManager->initialize($input->getOption('session'));

        return 0;
    }
}
