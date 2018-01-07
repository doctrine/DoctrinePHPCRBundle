<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand as BaseInitDoctrineDbalCommand;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

class InitDoctrineDbalCommand extends BaseInitDoctrineDbalCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:init:dbal')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $sessionName = $input->getOption('session');
        if (empty($sessionName)) {
            $container = $application->getKernel()->getContainer();
            $sessionName = $container->getParameter('doctrine_phpcr.default_session');
        }

        DoctrineCommandHelper::setApplicationConnection($application, $sessionName);

        parent::execute($input, $output);
    }
}
