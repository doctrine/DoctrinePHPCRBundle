<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\ODM\PHPCR\Mapping\MappingException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ODM\PHPCR\Tools\Console\Command\InfoDoctrineCommand as BaseInfoDoctrineCommand;

use Doctrine\Bundle\PHPCRBundle\OptionalCommand\InitDoctrineDbalCommand;
use Doctrine\Bundle\PHPCRBundle\OptionalCommand\JackrabbitCommand;

/**
 * Show information about mapped entities
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class InfoDoctrineCommand extends BaseInfoDoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:phpcr:mapping:info')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command.', null)
            ->setHelp(<<<EOT
The <info>doctrine:phpcr:mapping:info</info> shows basic information about which
entities exist and possibly if their mapping information contains errors or
not.

<info>php app/console doctrine:phpcr:mapping:info</info>

If you are using multiple document managers you can pick your choice with the
<info>--name</info> option:

<info>php app/console doctrine:phpcr:mapping:info --name=default</info>
EOT
        );
    }

    public function setApplication(\Symfony\Component\Console\Application $application = null)
    {
        parent::setApplication($application);

        if (class_exists('\Jackalope\Tools\Console\Command\JackrabbitCommand')) {
            $application->add(new JackrabbitCommand());
        }
        if (class_exists('\Jackalope\Tools\Console\Command\InitDoctrineDbalCommand')) {
            $application->add(new InitDoctrineDbalCommand());
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationDocumentManager($this->getApplication(), $input->getOption('name'));

        parent::execute($input, $output);
    }
}
