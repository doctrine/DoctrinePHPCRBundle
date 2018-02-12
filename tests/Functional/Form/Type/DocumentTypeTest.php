<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Component\Form\AbstractType;
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

    /**
     * @var bool whether we are dealing with legacy symfony form component that uses alias instead of class name for form types
     */
    private $legacy;

    public function setUp()
    {
        $this->legacy = !method_exists(AbstractType::class, 'getBlockPrefix');

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
        return self::$kernel->getContainer()->get('form.factory')->createBuilder($this->legacy ? 'form' : FormType::class, $data, $options);
    }

    /**
     * Render a form and return the HTML.
     */
    private function renderForm(FormBuilderInterface $formBuilder)
    {
        $formView = $formBuilder->getForm()->createView();
        /** @var Environment $twig */
        $twig = self::$kernel->getContainer()->get('twig');

        return $twig->render('form.html.twig', ['form' => $formView]);
    }

    public function testUnfiltered()
    {
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', $this->legacy ? 'phpcr_document' : DocumentType::class, [
                'class' => TestDocument::class,
            ])
        ;

        $html = $this->renderForm($formBuilder);
        $this->assertContains('<select id="form_single" name="form[single]"', $html);
        $this->assertContains('<option value="/test/doc"', $html);
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
            ->add('single', $this->legacy ? 'phpcr_document' : DocumentType::class, [
                'class' => TestDocument::class,
                'query_builder' => $qb,
            ])
        ;

        $html = $this->renderForm($formBuilder);
        $this->assertContains('<select id="form_single" name="form[single]"', $html);
        $this->assertNotContains('<option', $html);
    }
}
