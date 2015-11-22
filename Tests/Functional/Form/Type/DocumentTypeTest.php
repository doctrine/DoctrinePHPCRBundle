<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form\Type;

use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentTypeTest extends BaseTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var ReferrerDocument
     */
    private $referrer;

    private $legacy;

    public function setUp()
    {
        $this->legacy = !method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');

        $this->db('PHPCR')->loadFixtures([
            'Doctrine\Bundle\PHPCRBundle\Tests\Resources\DataFixtures\PHPCR\LoadData',
        ]);
        $this->dm = $this->db('PHPCR')->getOm();
        $document = $this->dm->find(null, '/test/doc');
        $this->assertNotNull($document, 'fixture loading not working');
        $this->referrer = $this->dm->find(null, '/test/ref');
        $this->assertNotNull($this->referrer, 'fixture loading not working');
    }

    /**
     * @return FormBuilderInterface
     */
    private function createFormBuilder($data, $options = array())
    {
        return $this->container->get('form.factory')->createBuilder($this->legacy ? 'form' : 'Symfony\Component\Form\Extension\Core\Type\FormType', $data, $options);
    }

    /**
     * Render a form and return the HTML.
     */
    private function renderForm(FormBuilderInterface $formBuilder)
    {
        $formView = $formBuilder->getForm()->createView();
        $templating = $this->getContainer()->get('templating');

        return $templating->render('::form.html.twig', array('form' => $formView));
    }

    public function testUuid()
    {
        $document = $this->dm->find(null, '/test/doc');
        $uuid = $document->uuid;

        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', $this->legacy ? 'phpcr_document' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType', array(
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
                'choice_value' => 'uuid'
            ))
        ;

        $form = $formBuilder->getForm();

        $form->submit(array(
            'single' => $uuid
        ));

        $this->assertInstanceOf('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument', $this->referrer->getSingle());
        $this->assertEquals('doc', $this->referrer->getSingle()->nodename);

        $html = $this->renderForm($formBuilder);
        $this->assertContains('<select id="form_single" name="form[single]"', $html);
        $this->assertContains(sprintf('<option value="%s"', $uuid), $html);
    }

    public function testUnfiltered()
    {
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', $this->legacy ? 'phpcr_document' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType', array(
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
            ));

        $html = $this->renderForm($formBuilder);
        $this->assertContains('<select id="form_single" name="form[single]"', $html);
        $this->assertContains('<option value="/test/doc"', $html);
    }

    public function testFiltered()
    {
        $qb = $this->dm
            ->getRepository('Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument')
            ->createQueryBuilder('e');
        $qb->where()->eq()->field('e.text')->literal('thiswillnotmatch');
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', $this->legacy ? 'phpcr_document' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType', array(
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
                'query_builder' => $qb,
            ));

        $html = $this->renderForm($formBuilder);
        $this->assertContains('<select id="form_single" name="form[single]"', $html);
        $this->assertNotContains('<option', $html);
    }
}
