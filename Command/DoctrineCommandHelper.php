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

use Symfony\Component\Console\Application;

use Doctrine\ODM\PHPCR\Tools\Console\Helper\DocumentManagerHelper;

use Jackalope\Tools\Console\Helper\DoctrineDbalHelper;
use Jackalope\Tools\Console\Helper\JackrabbitHelper;
use Jackalope\Session;

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

        if ($session instanceof Session) {
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
