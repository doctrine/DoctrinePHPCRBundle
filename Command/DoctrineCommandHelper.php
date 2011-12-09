<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Symfony\Component\Console\Application;

use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper;

use Jackalope\Tools\Console\Helper\DoctrineDbalHelper;
use Jackalope\Tools\Console\Helper\JackrabbitHelper;
use Jackalope;

/**
 * Provides some helper and convenience methods to configure doctrine commands in the context of bundles
 * and multiple sessions/document managers.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class DoctrineCommandHelper
{
    static public function setApplicationPHPCRSession(Application $application, $connName)
    {
        $service = null === $connName ? 'doctrine_phpcr.session' : 'doctrine_phpcr.'.$connName.'_session';
        $session = $application->getKernel()->getContainer()->get($service);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new DocumentManagerHelper($session));

        if ($session instanceof Jackalope\Session) {
            switch (get_class($session->getTransport())) {
                case 'Jackalope\Transport\DoctrineDBAL\Client':
                    $helperSet->set(new DoctrineDBALHelper($session->getTransport()->getConnection()));
                    break;
            }
        }
    }

    static public function setApplicationDocumentManager(Application $application, $dmName)
    {
        $service = null === $dmName ? 'doctrine_phpcr.odm.document_manager' : 'doctrine_phpcr.odm.'.$dmName.'_document_manager';
        $documentManager = $application->getKernel()->getContainer()->get($service);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new DocumentManagerHelper(null, $documentManager));
    }
}
