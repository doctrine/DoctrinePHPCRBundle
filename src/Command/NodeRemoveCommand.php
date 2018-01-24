<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\NodeRemoveCommand as BaseNodeRemoveCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeRemoveCommand extends BaseNodeRemoveCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:node:remove')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command')
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

        return parent::execute($input, $output);
    }
}
