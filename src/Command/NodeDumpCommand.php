<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\NodeDumpCommand as BaseDumpCommand;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeDumpCommand extends BaseDumpCommand implements ContainerAwareInterface
{
    private ContainerInterface $container;
    private PhpcrConsoleDumperHelper $consoleDumper;

    protected function getContainer(): ContainerInterface
    {
        if (null === $this->container) {
            $this->container = $this->getApplication()->getKernel()->getContainer();
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        if (!$container) {
            unset($this->container);

            return;
        }

        $this->container = $container;
    }

    public function setConsoleDumper(PhpcrConsoleDumperHelper $consoleDumper): void
    {
        $this->consoleDumper = $consoleDumper;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:node:dump')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $application,
            $input->getOption('session')
        );
        $helperSet = $application->getHelperSet();
        $helperSet->set($this->consoleDumper);

        if (!$input->hasOption('max_line_length')
            && $this->getContainer()->hasParameter('doctrine_phpcr.dump_max_line_length')
        ) {
            $input->setOption('max_line_length', $this->getContainer()->getParameter('doctrine_phpcr.dump_max_line_length'));
        }

        return parent::execute($input, $output);
    }
}
