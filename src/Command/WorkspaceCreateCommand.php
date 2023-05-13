<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\WorkspaceCreateCommand as BaseWorkspaceCreateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 */
class WorkspaceCreateCommand extends BaseWorkspaceCreateCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:workspace:create')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(),
            $input->getOption('session'),
            true
        );

        return parent::execute($input, $output);
    }
}
