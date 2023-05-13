<?php

namespace Doctrine\Bundle\PHPCRBundle\Migrator;

use PHPCR\SessionInterface;
use PHPCR\Util\TraversingItemVisitor;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMigrator extends TraversingItemVisitor implements MigratorInterface
{
    protected SessionInterface $session;
    protected OutputInterface $output;

    public function init(SessionInterface $session, OutputInterface $output): void
    {
        $this->session = $session;
        $this->output = $output;
    }
}
