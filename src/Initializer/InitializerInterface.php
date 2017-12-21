<?php


namespace Doctrine\Bundle\PHPCRBundle\Initializer;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

/**
 * Interface for initializers.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
interface InitializerInterface
{
    /**
     * This method should be used to establish the requisite
     * structure needed by the application or bundle of the
     * content repository.
     *
     * @param ManagerRegistry $registry
     */
    public function init(ManagerRegistry $registry);

    /**
     * Return a name which can be used to identify this initializer.
     *
     * @return string
     */
    public function getName();
}
