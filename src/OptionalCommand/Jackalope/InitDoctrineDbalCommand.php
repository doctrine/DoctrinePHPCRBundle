<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope;

use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;
use Jackalope\Tools\Console\Command\InitDoctrineDbalCommand as BaseInitDoctrineDbalCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 */
class InitDoctrineDbalCommand extends BaseInitDoctrineDbalCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:init:dbal')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
        ;
    }


    /**
     * @return int
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

        return parent::execute($input, $output);
    }
}
