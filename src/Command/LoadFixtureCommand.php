<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor;
use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerManager;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper;
use InvalidArgumentException;
use PHPCR\Util\Console\Command\BaseCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to load PHPCR-ODM fixtures.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class LoadFixtureCommand extends BaseCommand
{
    private const NAME = 'doctrine:phpcr:fixtures:load';

    public function __construct(
        private InitializerManager $initializerManager
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Load data fixtures to your PHPCR database.')
            ->addOption('fixtures', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The directory or file to load data fixtures from.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures to the existing data - will not purge the workspace.')
            ->addOption('no-initialize', null, InputOption::VALUE_NONE, 'Do not run the repository initializers after purging the repository.')
            ->addOption('dm', null, InputOption::VALUE_REQUIRED, 'The document manager to use for this command')
            ->setHelp(<<<'EOT'
The <info>doctrine:phpcr:fixtures:load</info> command loads data fixtures from
your bundles DataFixtures/PHPCR directory:

  <info>./app/console doctrine:phpcr:fixtures:load</info>

You can also optionally specify the path to fixtures with the
<info>--fixtures</info> option:

  <info>./app/console doctrine:phpcr:fixtures:load --fixtures=/path/to/fixtures1 --fixtures=/path/to/fixtures2</info>

If you want to append the fixtures instead of flushing the database first you
can use the <info>--append</info> option:

  <info>./app/console doctrine:phpcr:fixtures:load --append</info>

The <info>--dm</info> specifies wich documentmanager to use.
  <info>./app/console doctrine:phpcr:fixtures:load --dm="mydm"</info>

This command will also execute any registered Initializer classes after
purging.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dmName = $input->getOption('dm'); // defaults to null
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new \InvalidArgumentException('Expected to find '.Application::class.' but got '.
                ($application ? \get_class($application) : null));
        }
        DoctrineCommandHelper::setApplicationDocumentManager(
            $application,
            $dmName
        );

        $dm = $this->getPhpcrHelper()->getDocumentManager();
        $noInitialize = $input->getOption('no-initialize');

        if ($input->isInteractive() && !$input->getOption('append')) {
            $question = '<question>Careful, database will be purged. Do you want to continue Y/N ?</question>';
            $questionHelper = $this->getQuestionHelper();
            $question = new ConfirmationQuestion($question, false);
            $result = $questionHelper->ask($input, $output, $question);
            if (!$result) {
                return 0;
            }
        }

        $dirOrFile = $input->getOption('fixtures');
        if ($dirOrFile) {
            $paths = \is_array($dirOrFile) ? $dirOrFile : [$dirOrFile];
        } else {
            $kernel = $application->getKernel();
            $projectDir = method_exists($kernel, 'getRootDir') ? $kernel->getRootDir() : $kernel->getProjectDir().'/src';
            $paths = [$projectDir.'/DataFixtures/PHPCR'];
            foreach ($kernel->getBundles() as $bundle) {
                $paths[] = $bundle->getPath().'/DataFixtures/PHPCR';
            }
        }

        $loader = new Loader();
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            } elseif (is_file($path)) {
                $loader->loadFromFile($path);
            }
        }

        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $purger = new PHPCRPurger($dm);

        $executor = new PHPCRExecutor($dm, $purger, $noInitialize ? null : $this->initializerManager);
        $executor->setLogger(function ($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($fixtures, $input->getOption('append'));

        return 0;
    }

    protected function getPhpcrHelper(): DocumentManagerHelper
    {
        $helper = parent::getPhpcrHelper();
        \assert($helper instanceof DocumentManagerHelper);

        return $helper;
    }
}
