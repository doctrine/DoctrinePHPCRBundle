<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use PHPCR\Util\Console\Command\NodeTypeRegisterCommand as BaseRegisterNodeTypesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper to use this command in the symfony console with multiple sessions.
 */
class NodeTypeRegisterCommand extends BaseRegisterNodeTypesCommand
{
    private const BUNDLE_NT_PATH = 'Resources/config/phpcr-node-types';

    protected function configure(): void
    {
        parent::configure();
        $newHelp = <<<'EOT'


If no cnd-files are specified, the command will automatically try and find node files in the
<comment>%s</comment> directory of activated bundles.
EOT;
        $help = $this->getHelp().sprintf($newHelp, self::BUNDLE_NT_PATH);

        $this
            ->setName('doctrine:phpcr:node-type:register')
            ->addOption('session', null, InputOption::VALUE_REQUIRED, 'The session to use for this command')
            ->setHelp($help)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new \InvalidArgumentException('Expected to find '.Application::class.' but got '.
                ($application ? get_class($application) : null ));
        }

        DoctrineCommandHelper::setApplicationPHPCRSession(
            $application,
            $input->getOption('session'),
            true
        );

        $definitions = $input->getArgument('cnd-file');

        // if no cnd-files, automatically load from bundles
        if (0 === \count($definitions)) {
            $bundles = $application->getKernel()->getBundles();

            $candidatePaths = [];
            foreach ($bundles as $bundle) {
                $candidatePath = sprintf('%s/%s', $bundle->getPath(), self::BUNDLE_NT_PATH);

                if (!file_exists($candidatePath)) {
                    continue;
                }

                $candidatePaths[] = $candidatePath;
            }

            if (0 === \count($candidatePaths)) {
                $output->writeln(sprintf(
                    'No definition files specified and could not find any definitions in any <comment><bundle>/%s</comment> folders. Aborting.',
                    self::BUNDLE_NT_PATH
                ));

                return 1;
            }

            $input->setArgument('cnd-file', $candidatePaths);
        }

        return parent::execute($input, $output);
    }
}
