<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope;

use Jackalope\Tools\Console\Command\JackrabbitCommand as BaseJackrabbitCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class JackrabbitCommand extends BaseJackrabbitCommand implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            $this->container = $this->getApplication()->getKernel()->getContainer();
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:jackrabbit')
            ->setHelp(<<<'EOF'
The <info>doctrine:phpcr:jackrabbit</info> command allows to have a minimal control on the Jackrabbit server from within a
Symfony 2 command.

If the <info>jackrabbit_jar</info> option is set, it will be used as the Jackrabbit server jar file.
Otherwise you will have to set the doctrine_phpcr.jackrabbit_jar config parameter to a valid Jackrabbit
server jar file.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->getContainer()->hasParameter('doctrine_phpcr.jackrabbit_jar')) {
            $this->setJackrabbitPath($this->getContainer()->getParameter('doctrine_phpcr.jackrabbit_jar'));
        }

        if ($this->getContainer()->hasParameter('doctrine_phpcr.workspace_dir')) {
            $this->setWorkspaceDir($this->getContainer()->getParameter('doctrine_phpcr.workspace_dir'));
        }

        return parent::execute($input, $output);
    }
}
