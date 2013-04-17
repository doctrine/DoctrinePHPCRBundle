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

use Doctrine\ODM\PHPCR\Tools\Console\Command\RegisterSystemNodeTypesCommand as BaseRegisterSystemNodeTypesCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Command to collect init operations from any interested bundles and the
 * phpcr-odm itself.
 */
class InitCommand extends BaseRegisterSystemNodeTypesCommand implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            $this->container = $this->getApplication()->getKernel()->getContainer();
        }

        return $this->container;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:phpcr:init')
            ->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command')
            ->setDescription('Initialize the PHPCR repository.')
            ->setHelp(<<<EOT
Initialize the PHPCR repository with node types and base paths provided by the bundles.
Bundles can provide services tagged with doctrine_phpcr.initializer to provide their information.
EOT
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationPHPCRSession($this->getApplication(), $input->getOption('session'));

        parent::execute($input, $output);

        foreach ($this->getContainer()->getParameter('doctrine_phpcr.initialize.initializers') as $id) {
            $initializer = $this->getContainer()->get($id);
            $initializer->init($session = $this->getHelper('phpcr')->getSession());
        }

        return 0;
    }
}
