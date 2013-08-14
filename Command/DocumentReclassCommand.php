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

use PHPCR\Util\Console\Command\NodesUpdateCommand as BaseNodesUpdateCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DocumentReclassCommand extends BaseNodesUpdateCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:phpcr:document:reclass')
            ->addOption(
                'session', null, 
                InputOption::VALUE_OPTIONAL, 
                'The session to use for this command'
            )
            ->addArgument('classname', InputArgument::REQUIRED, 'Class name to change')
            ->addArgument('new-classname', InputArgument::REQUIRED, 'Classname to change to')
        ;
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


        $input->setOption('query', sprintf(
            'SELECT * FROM [nt:unstructured] WHERE [phpcr:class] = "%s"',
            $classname
        ));
        $input->setOption('apply-closure', array(
            function ($session, $node) use ($classname, $newClassname) {
                $node->setProperty('phpcr:class', $newClassname); 
                $node->setProperty('phpcr:classparents', array_reverse(class_parents($newClassname)));
            }
        ));

        parent::execute($input, $output);
    }
}

