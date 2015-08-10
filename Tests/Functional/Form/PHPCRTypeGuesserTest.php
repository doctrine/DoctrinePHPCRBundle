<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument;

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

    public function setUp()
    {
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
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
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
            '\Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            array(
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('date'),
            '\Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('text'),
            '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('number'),
            '\Symfony\Component\Form\Extension\Core\Type\NumberType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('integer'),
            '\Symfony\Component\Form\Extension\Core\Type\IntegerType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('long'),
            '\Symfony\Component\Form\Extension\Core\Type\IntegerType',
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
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'checkbox',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mdate'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'datetime',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mtext'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'text',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mnumber'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'number',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('minteger'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'integer',
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('mlong'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'type' => 'integer',
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
            '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('parent'),
            'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('nodename'),
            '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'required' => true,
            )
        );

        $this->assertFormType(
            $formBuilder->get('uuid'),
            '\Symfony\Component\Form\Extension\Core\Type\TextType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('child'),
            'Doctrine\Bundle\PHPCRBundle\Form\Type\PathType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
            )
        );

        $this->assertFormType(
            $formBuilder->get('children'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                'type' => 'phpcr_odm_path',
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
            '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('documents'),
            '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocument'),
            '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('testDocuments'),
            '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
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
            '\Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType',
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument',
            )
        );

        $this->assertFormType(
            $formBuilder->get('mixedReferrers'),
            '\Symfony\Component\Form\Extension\Core\Type\CollectionType',
            array(
                'attr' => array('readonly' => 'readonly'),
                'required' => false,
                'type' => 'phpcr_odm_path',
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
