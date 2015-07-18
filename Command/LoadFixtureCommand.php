<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use InvalidArgumentException;
use Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor;

/**
 * Command to load PHPCR-ODM fixtures.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class LoadFixtureCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:phpcr:fixtures:load')
            ->setDescription('Load data fixtures to your PHPCR database.')
            ->addOption('fixtures', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory or file to load data fixtures from.')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures to the existing data - will not purge the workspace.')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Deprecated (was never used)')
            ->addOption('no-initialize', null, InputOption::VALUE_NONE, 'Do not run the repository initializers after purging the repository.')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command (deprecated, alias for dm)')
            ->addOption('dm', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command')
            ->setHelp(<<<EOT
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

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('name')) {
            trigger_error(
                'The name attribute for command doctrine:phpcr:fixtures:load is deprecated. It was never used.',
                E_USER_DEPRECATED
            );
        }

        if ($input->getOption('dm')) {
            $dmName = $input->getOption('dm');
        } elseif ($input->getOption('session')) {
            $dmName = $input->getOption('session');
            trigger_error(
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

        $dm = $this->getHelperSet()->get('phpcr')->getDocumentManager();
        $noInitialize = $input->getOption('no-initialize');

        if ($input->isInteractive() && !$input->getOption('append')) {
            /** @var $dialog DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>Careful, database will be purged. Do you want to continue Y/N ?</question>', false)) {
                return 0;
            }
        }

        $dirOrFile = $input->getOption('fixtures');
        if ($dirOrFile) {
            $paths = is_array($dirOrFile) ? $dirOrFile : array($dirOrFile);
        } else {
            $paths = array();
            foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
                $paths[] = $bundle->getPath().'/DataFixtures/PHPCR';
            }
        }

        $loader = new ContainerAwareLoader($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }

        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $purger = new PHPCRPurger($dm);

        if ($noInitialize) {
            $initializerManager = null;
        } else {
            $initializerManager = $this->getContainer()->get('doctrine_phpcr.initializer_manager');
        }

        $executor = new PHPCRExecutor($dm, $purger, $initializerManager);
        $executor->setLogger(function ($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($fixtures, $input->getOption('append'));

        return 0;
    }
}
