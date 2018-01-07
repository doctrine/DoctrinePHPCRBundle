<?php

namespace Doctrine\Bundle\PHPCRBundle\Initializer;

/**
 * Interface for session aware intiializers.
 * Handles running initializer in specified session
 * instead of the default one.
 *
 * @author michalpolko <michalpolko@o2.pl>
 */
interface SessionAwareInitializerInterface
{
    /**
     * Set session name for this initilizer.
     *
     * @param string $sessionName
     */
    public function setSessionName($sessionName);
}
