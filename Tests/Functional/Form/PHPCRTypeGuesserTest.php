<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument;
use Symfony\Component\HttpKernel\Kernel;

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
    private $entryTypeOption;

    public function setUp()
    {
        $this->entryTypeOption = Kernel::VERSION_ID < 20800 ? 'type' : 'entry_type';

        $this->db('PHPCR')->loadFixtures(array(
            'Doctrine\Bundle\PHPCRBundle\Tests\Resources\DataFixtures\PHPCR\LoadData',
        ));
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
        return $this->container->get('form.factory')->createBuilder(Kernel::VERSION_ID < 20800 ? 'form' : 'Symfony\Component\Form\Extension\Core\Type\FormType', $data, $options);
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
            Kernel::VERSION_ID < 20800 ? 'checkbox' : '\Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            array(
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('date'),
            Kernel::VERSION_ID < 20800 ? 'datetime' : '\Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('text'),
            Kernel::VERSION_ID < 20800 ? 'text' : '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('number'),
            Kernel::VERSION_ID < 20800 ? 'number' : '\Symfony\Component\Form\Extension\Core\Type\NumberType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('integer'),
            Kernel::VERSION_ID < 20800 ? 'integer' : '\Symfony\Component\Form\Extension\Core\Type\IntegerType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('long'),
            Kernel::VERSION_ID < 20800 ? 'integer' : '\Symfony\Component\Form\Extension\Core\Type\IntegerType',
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
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'checkbox' : 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mdate'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'datetime' : 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mtext'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mnumber'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'number' : 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('minteger'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'integer' : 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mlong'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'integer' : 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
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
            Kernel::VERSION_ID < 20800 ? 'text' : '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('parent'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('nodename'),
            Kernel::VERSION_ID < 20800 ? 'text' : '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('uuid'),
            Kernel::VERSION_ID < 20800 ? 'text' : '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('child'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('children'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'phpcr_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            )
        );

        $this->renderForm($formBuilder);
    }

    public function testReference()
    {
        $formBuilder = $this->createFormBuilder($this->referrer);

        $formBuilder
            ->add('single', null, array('class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument'))
            ->add('documents', null, array('class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument'))
            ->add('testDocument')
            ->add('testDocuments')
        ;

        $this->assertFormType(
            $formBuilder->get('single'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_odm_document' : '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('documents'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_odm_document' : '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocument'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_odm_document' : '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocuments'),
            Kernel::VERSION_ID < 20800 ? 'phpcr_odm_document' : '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
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
            Kernel::VERSION_ID < 20800 ? 'phpcr_odm_document' : '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('mixedReferrers'),
            Kernel::VERSION_ID < 20800 ? 'collection' : '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                $this->entryTypeOption => Kernel::VERSION_ID < 20800 ? 'phpcr_path' : 'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
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
