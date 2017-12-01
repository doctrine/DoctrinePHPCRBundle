<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\ChoiceList;

use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
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
        $this->db('PHPCR')->loadFixtures(array(LoadData::class));

        $this->dm = $this->getContainer()->get('doctrine_phpcr.odm.default_document_manager');
    }

    public function testGetByIds()
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $ids = array('/test/doc', '/test/doc2', '/test/doc3');
        $documents = $loader->getEntitiesByIds('id', $ids);
        $this->assertCount(2, $documents);
        foreach ($documents as $i => $document) {
            $this->assertInstanceOf(TestDocument::class, $document);
            $this->assertTrue(in_array($document->id, $ids));
        }
    }

    public function testGetByIdsNotFound()
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', array('/foo/bar'));
        $this->assertCount(0, $documents);
    }

    public function testGetByIdsFilter()
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', array('/test/doc'));
        $this->assertCount(0, $documents);
    }
}
