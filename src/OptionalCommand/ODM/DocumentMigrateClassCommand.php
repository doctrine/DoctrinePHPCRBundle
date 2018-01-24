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
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('dm', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command (deprecated, alias for dm)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dm')) {
            $dmName = $input->getOption('dm');
        } elseif ($input->getOption('session')) {
            $dmName = $input->getOption('session');
            @trigger_error(
                'The session attribute for command doctrine:phpcr:fixtures:load is deprecated. Use --dm instead.',
                E_USER_DEPRECATED
            );
        } else {
            $dmName = null;
        }
        DoctrineCommandHelper::setApplicationDocumentManager(
            $this->getApplication(),
            $dmName
        );

        return parent::execute($input, $output);
    }
}
