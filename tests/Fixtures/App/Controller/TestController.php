<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Controller;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Do a simple PHPCR request.
     */
    public function phpcrRequest()
    {
        $dm = $this->registry->getManager();

        $document = $dm->find(null, '/foo');

        if (null !== $document) {
            $dm->remove($document);
            $dm->flush();
        }

        $document = new ReferrerDocument();
        $document->id = '/foo';

        $dm->persist($document);
        $dm->flush();

        return $this->render('foo.html');
    }
}
