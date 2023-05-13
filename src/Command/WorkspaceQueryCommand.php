<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\WorkspaceQueryCommand as BaseWorkspaceQueryCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class WorkspaceQueryCommand extends BaseWorkspaceQueryCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:workspace:query')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(),
            $input->getOption('session')
        );

        return parent::execute($input, $output);
    }
}
