<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Twig\Environment;

class DocumentTypeTest extends BaseTestCase
{
    /**
     * @var DocumentManagerInterface
     */
    private $dm;

    /**
     * @var ReferrerDocument
     */
    private $referrer;

    public function setUp(): void
    {
        self::bootKernel();

        $repositoryManager = $this->getRepositoryManager();
        $repositoryManager->loadFixtures([LoadData::class]);
        $this->dm = $repositoryManager->getDocumentManager();
        $document = $this->dm->find(null, '/test/doc');
        $this->assertNotNull($document, 'fixture loading not working');
        $this->referrer = $this->dm->find(null, '/test/ref');
        $this->assertNotNull($this->referrer, 'fixture loading not working');
    }

    /**
     * @return FormBuilderInterface
     */
    private function createFormBuilder($data, $options = [])
    {
        return self::getTestContainer()->get('form.factory')->createBuilder(FormType::class, $data, $options);
    }

    /**
     * Render a form and return the HTML.
     */
    private function renderForm(FormBuilderInterface $formBuilder)
    {
        $formView = $formBuilder->getForm()->createView();
        /** @var Environment $twig */
        $twig = self::getTestContainer()->get('twig');

        return $twig->render('form.html.twig', ['form' => $formView]);
    }

    public function testUnfiltered()
    {
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', DocumentType::class, [
                'class' => TestDocument::class,
            ])
        ;

        $html = $this->renderForm($formBuilder);
        $this->assertStringContainsString('<select id="form_single" name="form[single]"', $html);
        $this->assertStringContainsString('<option value="/test/doc"', $html);
    }

    public function testFiltered()
    {
        $qb = $this->dm
            ->getRepository(TestDocument::class)
            ->createQueryBuilder('e')
        ;
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', DocumentType::class, [
                'class' => TestDocument::class,
                'query_builder' => $qb,
            ])
        ;

        $html = $this->renderForm($formBuilder);
        $this->assertStringContainsString('<select id="form_single" name="form[single]"', $html);
        $this->assertStringNotContainsString('<option', $html);
    }
}
