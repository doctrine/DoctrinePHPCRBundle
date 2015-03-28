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
        $qb = $this->dm->getRepository('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument')->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', array('/test/doc'));
        $this->assertCount(1, $documents);
        $doc = reset($documents);
        $this->assertInstanceOf('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument', $doc);
    }

    public function testGetByIdsNotFound()
    {
        $qb = $this->dm->getRepository('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument')->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', array('/foo/bar'));
        $this->assertCount(0, $documents);
    }

    public function testGetByIdsFilter()
    {
        $qb = $this->dm->getRepository('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument')->createQueryBuilder('e');
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', array('/test/doc'));
        $this->assertCount(0, $documents);
    }
}
