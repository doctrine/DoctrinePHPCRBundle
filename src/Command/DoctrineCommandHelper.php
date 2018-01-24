<?php

namespace Doctrine\Bundle\PHPCRBundle\Command;

use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper;
use Doctrine\ODM\PHPCR\Version;
use Jackalope\Session as JackalopeSession;
use Jackalope\Tools\Console\Helper\DoctrineDbalHelper;
use Jackalope\Transport\DoctrineDBAL\Client as DbalClient;
use Jackalope\Transport\DoctrineDBAL\LoggingClient as DbalLoggingClient;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Provides helper methods to configure doctrine PHPCR-ODM commands
 * in the context of bundles and multiple sessions/document managers.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class DoctrineCommandHelper
{
    /**
     * Prepare just the DBAL connection for the init command where no session is available yet.
     *
     * @param Application $application
     * @param string      $sessionName
     */
    public static function setApplicationConnection(Application $application, $sessionName)
    {
        $connectionService = sprintf('doctrine_phpcr.jackalope_doctrine_dbal.%s_connection', $sessionName);
        $helperSet = $application->getHelperSet();
        $helperSet->set(new DoctrineDBALHelper($application->getKernel()->getContainer()->get($connectionService)));
    }

    /**
     * Set the PHPCR session and connection.
     *
     * @param Application $application
     * @param string      $sessionName
     * @param bool        $admin
     */
    public static function setApplicationPHPCRSession(Application $application, $sessionName, $admin = false)
    {
        $registry = $application->getKernel()->getContainer()->get('doctrine_phpcr');
        $session = $admin ? $registry->getAdminConnection($sessionName) : $registry->getConnection($sessionName);

        $helperSet = $application->getHelperSet();
        if (class_exists(Version::class)) {
            $helperSet->set(new DocumentManagerHelper($session));
        } else {
            $helperSet->set(new PhpcrHelper($session));
        }

        if ($session instanceof JackalopeSession
            && ($session->getTransport() instanceof DbalClient
                || $session->getTransport() instanceof DbalLoggingClient
            )
        ) {
            $helperSet->set(new DoctrineDBALHelper($session->getTransport()->getConnection()));
        }
    }

    /**
     * Set the document manager on the application.
     *
     * @param Application $application
     * @param string      $dmName
     */
    public static function setApplicationDocumentManager(Application $application, $dmName)
    {
        /** @var $registry ManagerRegistry */
        $registry = $application->getKernel()->getContainer()->get('doctrine_phpcr');
        $documentManager = $registry->getManager($dmName);

        $helperSet = $application->getHelperSet();
        $helperSet->set(new DocumentManagerHelper(null, $documentManager));
    }
}
