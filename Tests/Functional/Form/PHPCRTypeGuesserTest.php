<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form;

use Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PathType;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;

class PHPCRTypeGuesserTest extends BaseTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var TestDocument
     */
    private $document;

    /**
     * @var ReferrerDocument
     */
    private $referrer;

    /**
     * Work with 2.3-2.7 and 3.0 at the same time. drop once we switch to symfony 3.0.
     */
    private $legacy;

    /**
     * Work with 2.3-2.7 and 3.0 at the same time. drop once we switch to symfony 3.0.
     */
    private $entryTypeOption;

    public function setUp()
    {
        $this->legacy = !method_exists(AbstractType::class, 'getBlockPrefix');
        $this->entryTypeOption = $this->legacy ? 'type' : 'entry_type';

        $this->db('PHPCR')->loadFixtures(array(LoadData::class));
        $this->dm = $this->db('PHPCR')->getOm();
        $this->document = $this->dm->find(null, '/test/doc');
        $this->assertNotNull($this->document, 'fixture loading not working');
        $this->referrer = $this->dm->find(null, '/test/ref');
        $this->assertNotNull($this->referrer, 'fixture loading not working');
    }

    /**
     * @return FormBuilderInterface
     */
    private function createFormBuilder($data, $options = array())
    {
        return $this->container->get('form.factory')->createBuilder($this->legacy ? 'form' : FormType::class, $data, $options);
    }

    public function testFields()
    {
        $formBuilder = $this->createFormBuilder($this->document);

        $formBuilder
            ->add('bool')
            ->add('date')
            ->add('text')
            ->add('number')
            ->add('long')
            ->add('integer')
        ;
        // binary has to be handled as nt:file child.

        $this->assertFormType(
            $formBuilder->get('bool'),
            CheckboxType::class,
            array(
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('date'),
            DateTimeType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('text'),
            TextType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('number'),
            NumberType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('integer'),
            IntegerType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('long'),
            IntegerType::class,
            array(
                'required' => true,
            )
        );

        $this->renderForm($formBuilder);
    }

    public function testMultivalueFields()
    {
        $formBuilder = $this->createFormBuilder($this->document);

        $formBuilder
            ->add('mbool')
            ->add('mdate')
            ->add('mtext')
            ->add('mnumber')
            ->add('mlong')
            ->add('minteger')
        ;

        $this->assertFormType(
            $formBuilder->get('mbool'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'checkbox' : CheckboxType::class,
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mdate'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'datetime' : DateTimeType::class,
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mtext'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'text' : TextType::class,
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mnumber'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'number' : NumberType::class,
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('minteger'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'integer' : IntegerType::class,
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mlong'),
            CollectionType::class,
            array(
                $this->entryTypeOption => $this->legacy ? 'integer' : IntegerType::class,
                'required' => false,
            )
        );

        $this->renderForm($formBuilder);
    }

    public function testHierarchy()
    {
        $formBuilder = $this->createFormBuilder($this->document);

        $formBuilder
            ->add('id')
            ->add('parent')
            ->add('nodename')
            ->add('uuid')
            ->add('child')
            ->add('children')
        ;

        $this->assertFormType(
            $formBuilder->get('id'),
            TextType::class,
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('parent'),
            PathType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('nodename'),
            TextType::class,
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('uuid'),
            TextType::class,
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('child'),
            PathType::class,
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('children'),
            CollectionType::class,
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                $this->entryTypeOption => $this->legacy ? 'phpcr_odm_path' : PathType::class,
            )
        );

        $this->renderForm($formBuilder);
    }

    public function testReference()
    {
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', null, array('class' => ReferrerDocument::class))
            ->add('documents', null, array('class' => ReferrerDocument::class))
            ->add('testDocument')
            ->add('testDocuments')
        ;

        $this->assertFormType(
            $formBuilder->get('single'),
            DocumentType::class,
            array(
                'required' => false,
                'class' => ReferrerDocument::class,
            )
        );

        $this->assertFormType(
            $formBuilder->get('documents'),
            DocumentType::class,
            array(
                'required' => false,
                'multiple' => true,
                'class' => ReferrerDocument::class,
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocument'),
            DocumentType::class,
            array(
                'required' => false,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocuments'),
            DocumentType::class,
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument',
            )
        );

        $this->renderForm($formBuilder);
    }

    public function testReferrers()
    {
        $formBuilder = $this->createFormBuilder($this->document);

        $formBuilder
            ->add('referrers')
            ->add('mixedReferrers')
        ;

        $this->assertFormType(
            $formBuilder->get('referrers'),
            DocumentType::class,
            array(
                'required' => false,
                'multiple' => true,
                'class' => ReferrerDocument::class,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mixedReferrers'),
            CollectionType::class,
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                $this->entryTypeOption => $this->legacy ? 'phpcr_odm_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            )
        );

        $this->renderForm($formBuilder);
    }

    /**
     * Sanity check: can the form really be built?
     *
     * We do not further test the generated html, that would be testing the
     * form component itself, which is done elsewhere.
     */
    private function renderForm(FormBuilderInterface $formBuilder)
    {
        $formView = $formBuilder->getForm()->createView();
        $templating = $this->getContainer()->get('templating');
        $templating->render('::form.html.twig', array('form' => $formView));
    }

    /**
     * Assert that the form element has an inner type of type $typeClass and
     * the specified options with their values.
     *
     * @param FormBuilderInterface $element
     * @param string               $typeClass FQN class
     * @param array                $options   keys are option names, values the
     *                                        expected option values
     */
    private function assertFormType(FormBuilderInterface $element, $typeClass, array $options)
    {
        $type = $element->getType()->getInnerType();
        $this->assertInstanceOf($typeClass, $type);
        foreach ($options as $option => $expected) {
            $this->assertEquals($expected, $element->getOption($option), "Option '$option' does not have the expected value '".serialize($expected)."'");
        }
    }
}
