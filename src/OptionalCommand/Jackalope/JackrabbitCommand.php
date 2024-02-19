<?php

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\Jackalope;

use Jackalope\Tools\Console\Command\JackrabbitCommand as BaseJackrabbitCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class JackrabbitCommand extends BaseJackrabbitCommand
{
    private const NAME = 'doctrine:phpcr:jackrabbit';

    public function __construct(
        private ?string $jackrabbitJar,
        private ?string $workspaceDir,
    ) {
        parent::__construct(self::NAME);
    }
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(self::NAME)
            ->setHelp(<<<'EOF'
The <info>doctrine:phpcr:jackrabbit</info> command allows to have a minimal control on the Jackrabbit server from within a
Symfony 2 command.

If the <info>jackrabbit_jar</info> option is set, it will be used as the Jackrabbit server jar file.
Otherwise, you will have to set the doctrine_phpcr.jackrabbit_jar config parameter to a valid Jackrabbit
server jar file.
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setJackrabbitPath($this->jackrabbitJar);
        $this->setWorkspaceDir($this->workspaceDir);

        return parent::execute($input, $output);
    }
}
