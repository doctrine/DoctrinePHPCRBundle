<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM;

use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;
use Doctrine\ODM\PHPCR\Tools\Console\Command\DocumentMigrateClassCommand as BaseDocumentMigrateClassCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DocumentMigrateClassCommand extends BaseDocumentMigrateClassCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('dm', null, InputOption::VALUE_REQUIRED, 'The document manager to use for this command');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dmName = $input->getOption('dm'); // defaults to null
        DoctrineCommandHelper::setApplicationDocumentManager(
            $this->getApplication(),
            $dmName
        );

        return parent::execute($input, $output);
    }
}
