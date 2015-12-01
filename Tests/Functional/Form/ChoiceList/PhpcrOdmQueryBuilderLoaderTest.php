<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\ChoiceList;

use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class PhpcrOdmQueryBuilderLoaderTest extends BaseTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Doctrine\Bundle\PHPCRBundle\Tests\Resources\DataFixtures\PHPCR\LoadData',
        ));

        $this->dm = $this->getContainer()->get('doctrine_phpcr.odm.default_document_manager');
    }

    public function testGetByIds()
    {
        $class = 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument';
        $qb = $this->dm->getRepository($class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm, $class);
        $ids = array('/test/doc', '/test/doc2', '/test/doc3');
        $documents = $loader->getEntitiesByIds('id', $ids);
        $this->assertCount(2, $documents);
        foreach ($documents as $i => $document) {
            $this->assertInstanceOf('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument', $document);
            $this->assertTrue(in_array($document->id, $ids));
        }
    }

    public function testGetByIdsWithUuid()
    {
        $class = 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument';
        $qb = $this->dm->getRepository($class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm, $class);

        $doc = $this->dm->find(null, '/test/doc');
        $uuids = array($doc->uuid, 'non-existing-uuid');

        $documents = $loader->getEntitiesByIds('uuid', $uuids);
        $this->assertCount(1, $documents);
        foreach ($documents as $i => $document) {
            $this->assertInstanceOf('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument', $document);
            $this->assertTrue(in_array($document->uuid, $uuids));
        }
    }

    public function testGetByIdsNotFound()
    {
        $class = 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument';
        $qb = $this->dm->getRepository($class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm, $class);
        $documents = $loader->getEntitiesByIds('id', array('/foo/bar'));
        $this->assertCount(0, $documents);
    }

    public function testGetByIdsFilter()
    {
        $class = 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument';
        $qb = $this->dm->getRepository('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument')->createQueryBuilder('e');
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm, $class);
        $documents = $loader->getEntitiesByIds('id', array('/test/doc'));
        $this->assertCount(0, $documents);
    }
}
