<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM;

use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;
use Doctrine\ODM\PHPCR\Tools\Console\Command\VerifyUniqueNodeTypesMappingCommand as BaseVerifyUniqueNodeTypesMappingCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the Symfony console with multiple sessions.
 */
class VerifyUniqueNodeTypesMappingCommand extends BaseVerifyUniqueNodeTypesMappingCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:mapping:verify-unique-node-types')
            ->setDescription('Verify that documents claiming to have unique node types are truly unique')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command checks all mapped PHPCR-ODM documents
and verifies that any claiming to use unique node types are truly unique.
EOT
            );
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationDocumentManager(
            $this->getApplication(),
            $input->getOption('session')
        );

        return parent::execute($input, $output);
    }
}
