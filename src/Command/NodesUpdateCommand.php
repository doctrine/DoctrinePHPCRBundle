<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\NodesUpdateCommand as BaseNodesUpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodesUpdateCommand extends BaseNodesUpdateCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:nodes:update')
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
