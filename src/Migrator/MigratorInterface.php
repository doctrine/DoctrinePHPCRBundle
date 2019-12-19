<?php

namespace Doctrine\Bundle\PHPCRBundle\Migrator;

use PHPCR\SessionInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface MigratorInterface
{
    public function init(SessionInterface $session, OutputInterface $output);

    /**
     * @param string $identifier
     * @param int    $depth
     *
     * @return int exit code
     */
    public function migrate($identifier = '/', $depth = -1);
}
