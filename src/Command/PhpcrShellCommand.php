<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Shell\Console\Application\SessionApplication;
use PHPCR\Shell\PhpcrShell;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 */
class PhpcrShellCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('doctrine:phpcr:shell');
        $this->addArgument('cmd', InputArgument::IS_ARRAY);
        $this->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command');
        $this->setDescription('Proxy for an embedded PHPCR Shell. Commands should be quoted');
        $this->setHelp(<<<'EOT'
This command will send commands to an embedded PHPCR shell. For it to work you
will need to have the phpcr-shell dependency installed.

To list the available sub-commands:

    <info>$ %command.full_name%</info>

Simple commands can be executed as follows:

    <info>$ %command.full_name% node:list</info>
    <info>$ %command.full_name% node:create foobar my:nodetype</info>
    <info>$ %command.full_name% session:namespace:set foo http://foobar.com/foo</info>

Due to limitations with the Symfony Console component, if you want to specify
options you will need to quote the entire sub-command:

    <info>$ %command.full_name% "node:list /path/to/some/node --level=3 --template"</info>

You can execute SELECT JCR-SQL2 queries as follows:

    <info>$ php app/console phpcr "SELECT * FROM [nt:unstructured]"</info>

NOTE: When executing single commands the session is saved automatically. This
      is in contrast to the shell, where the session has to be explicitly saved with
      the <info>session:save</info> command.
EOT
    );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!class_exists(SessionApplication::class)) {
            throw new \InvalidArgumentException(sprintf(
                'PHPCR-Shell not installed as a dependency. Add the "phpcr/phpcr-shell" to your '.
                'composer.json file to use this command'
            ));
        }

        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(),
            $input->getOption('session')
        );

        $args = $input->getArgument('cmd');
        $launchShell = empty($args);
        $session = $this->getHelper('phpcr')->getSession();

        // If no arguments supplied, launch the shell uwith the embedded application
        if ($launchShell) {
            $shell = PhpcrShell::createEmbeddedShell($session);
            $exitCode = $shell->run();

            return $exitCode;
        }

        // else try and run the command using the given input
        $application = PhpcrShell::createEmbeddedApplication($session);
        $exitCode = $application->runWithStringInput(implode(' ', $args), $output);

        // always save the session after running a single command
        $session->save();

        return $exitCode;
    }
}
