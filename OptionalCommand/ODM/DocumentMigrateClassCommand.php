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

namespace Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM;

use PHPCR\Util\Console\Command\NodesUpdateCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DocumentMigrateClassCommand extends NodesUpdateCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:phpcr:document:migrate-class')
            ->addOption(
                'session', null, 
                InputOption::VALUE_OPTIONAL, 
                'The session to use for this command'
            )
            ->setDescription('Command to migrate document classes.')

            ->addArgument('classname', InputArgument::REQUIRED, 'Old class name (does not need to exist in current codebase')
            ->addArgument('new-classname', InputArgument::REQUIRED, 'New class name (must exist in current codebase')
            ->setHelp(<<<HERE
The <info>doctrine:phpcr:docment:migrate-class</info> command migrates document classes matching the given old class name to given new class name.

    <info>$ php ./app/console/phpcr doctrine:phpcr:document:migrate-class "Old\\ClassName" "New\\ClassName"</info>
HERE
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::configure();
        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(), 
            $input->getOption('session')
        );

        $classname = $input->getArgument('classname');
        $newClassname = $input->getArgument('new-classname');

        if (!class_exists($newClassname)) {
            throw new \Exception(sprintf('New class name "%s" does not exist.',
                $newClassname
            ));
        }

        $classParents = array_reverse(class_parents($newClassname));

        $input->setOption('query', sprintf(
            'SELECT * FROM [nt:unstructured] WHERE [phpcr:class] = "%s"',
            $classname
        ));

        $input->setOption('apply-closure', array(
            function ($session, $node) use ($newClassname, $classParents) {
                $node->setProperty('phpcr:class', $newClassname); 
                $node->setProperty('phpcr:classparents', $classParents);
            }
        ));

        parent::execute($input, $output);
    }
}

