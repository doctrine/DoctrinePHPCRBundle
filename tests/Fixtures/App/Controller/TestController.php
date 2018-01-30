<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Controller;

use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    /**
     * Do a simple PHPCR request.
     */
    public function phpcrRequest()
    {
        $dm = $this->get('doctrine_phpcr')->getManager();

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
