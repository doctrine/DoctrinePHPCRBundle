<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\NodeDumpCommand as BaseDumpCommand;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeDumpCommand extends BaseDumpCommand
{
    private const NAME = 'doctrine:phpcr:node:dump';

    public function __construct(
        private PhpcrConsoleDumperHelper $consoleDumper,
        private int $dumpMaxLineLength
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(self::NAME)
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $application,
            $input->getOption('session')
        );
        $helperSet = $application->getHelperSet();
        $helperSet->set($this->consoleDumper);

        if (!$input->hasOption('max_line_length')) {
            $input->setOption('max_line_length', $this->dumpMaxLineLength);
        }

        return parent::execute($input, $output);
    }

    public function getApplication(): Application
    {
        $application = parent::getApplication();
        if (!$application instanceof Application) {
            throw new \InvalidArgumentException('Expected to find '.Application::class.' but got '.
                ($application ? \get_class($application) : null));
        }

        return $application;
    }
}
