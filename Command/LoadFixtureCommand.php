<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader;

use Doctrine\Bundle\PHPCRBundle\Helper\Fixtures\PHPCRExecutor;
use Doctrine\Bundle\PHPCRBundle\Helper\Fixtures\PHPCRPurger;

use PHPCR\Util\Console\Helper\ConsoleParametersParser;
use InvalidArgumentException;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class LoadFixtureCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:fixtures:load')
            ->setDescription('Load fixtures PHPCR files')
            ->addOption('dm', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'The path to the fixtures')
            ->addOption('purge', null, InputOption::VALUE_OPTIONAL, 'Set to true if the database must be purged')
            ->setHelp(<<<EOF
The <info>fixtures:load</info> command loads PHPCR fixtures
EOF
            )
        ;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationDocumentManager($this->getApplication(), $input->getOption('dm'));

        $path = $input->getOption('path');
        if (is_dir($path)) {
            $paths = array($path);
        } else {
            $paths = array();
            foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
                $paths[] = $bundle->getPath().'/DataFixtures/PHPCR';
            }
        }

        $purge = false;
        if ($purgeOption = $input->getOption('purge')) {
            $purge = ($purgeOption == '1' || ConsoleParametersParser::isTrueString($purgeOption));
        }

        $dm = $this->getHelper('phpcr')->getDocumentManager();

        $loader = new Loader($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }
        
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $purger = new PHPCRPurger();
        $executor = new PHPCRExecutor($dm, $purger);
        $executor->execute($fixtures, ! $purge);

        return 0;
    }
}
