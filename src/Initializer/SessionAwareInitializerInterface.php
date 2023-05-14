<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

/**
 * Handles running initializer in specified session instead of the default one.
 *
 * @author michalpolko <michalpolko@o2.pl>
 */
interface SessionAwareInitializerInterface
{
    public function setSessionName(string $sessionName): void;
}
