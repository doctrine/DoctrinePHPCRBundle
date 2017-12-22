<?php


namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ODM\PHPCR\Tools\Console\Command\InfoDoctrineCommand as BaseInfoDoctrineCommand;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

/**
 * Show information about mapped entities.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class InfoDoctrineCommand extends BaseInfoDoctrineCommand
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

<info>php app/console doctrine:phpcr:mapping:info --session=default</info>
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
