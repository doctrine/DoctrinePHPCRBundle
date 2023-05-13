<?php

namespace Doctrine\Bundle\PHPCRBundle\Migrator;

use PHPCR\SessionInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface MigratorInterface
{
    public function init(SessionInterface $session, OutputInterface $output): void;

    /**
     * @return int exit code
     */
    public function migrate(string $identifier = '/', int $depth = -1): int;
}
