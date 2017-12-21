<?php


namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ODM\PHPCR\Tools\Console\Command\DocumentConvertTranslationCommand as BaseDocumentConvertTranslationCommand;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

/**
 * Integrate command into symfony.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class DocumentConvertTranslationCommand extends BaseDocumentConvertTranslationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command.', null)
            ->setHelp($this->getHelp().<<<'EOT'

If you are using multiple document managers you can pick your choice with the
<info>--session</info> option:

<info>php app/console doctrine:phpcr:document:convert-translation --session=default</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
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
