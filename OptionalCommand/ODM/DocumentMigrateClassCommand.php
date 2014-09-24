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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ODM\PHPCR\Tools\Console\Command\DocumentMigrateClassCommand as BaseDocumentMigrateClassCommand;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DocumentMigrateClassCommand extends BaseDocumentMigrateClassCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();


        $this->addOption('dm', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The document manager to use for this command (deprecated, alias for dm)');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dm')) {
            $dmName = $input->getOption('dm');
        } else if($input->getOption('session')) {
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

        return parent::execute($input, $output);
    }
}

