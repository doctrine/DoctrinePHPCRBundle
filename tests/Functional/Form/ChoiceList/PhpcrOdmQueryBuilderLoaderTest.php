<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\ChoiceList;

use Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;

class PhpcrOdmQueryBuilderLoaderTest extends BaseTestCase
{
    private DocumentManagerInterface $dm;

    public function setUp(): void
    {
        self::bootKernel();

        $repositoryManager = $this->getRepositoryManager();
        $repositoryManager->loadFixtures([LoadData::class]);

        $this->dm = $repositoryManager->getDocumentManager();
    }

    public function testGetByIds(): void
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $ids = ['/test/doc', '/test/doc2', '/test/doc3'];
        $documents = $loader->getEntitiesByIds('id', $ids);
        $this->assertCount(2, $documents);
        foreach ($documents as $i => $document) {
            $this->assertInstanceOf(TestDocument::class, $document);
            $this->assertContains($document->id, $ids);
        }
    }

    public function testGetByIdsNotFound(): void
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', ['/foo/bar']);
        $this->assertCount(0, $documents);
    }

    public function testGetByIdsFilter(): void
    {
        $qb = $this->dm->getRepository(TestDocument::class)->createQueryBuilder('e');
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $loader = new PhpcrOdmQueryBuilderLoader($qb, $this->dm);
        $documents = $loader->getEntitiesByIds('id', ['/test/doc']);
        $this->assertCount(0, $documents);
    }
}
