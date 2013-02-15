<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Form\DataTransformer;

use Doctrine\Bundle\PHPCRBundle\Form\DataTransformer\PHPCRNodeToPathTransformer;
use Jackalope\Node;
use Doctrine\Bundle\PHPCRBundle\Form\Type\PHPCRReferenceType;

class PHPCRReferenceTypeTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->session = $this->getMock('PHPCR\SessionInterface');

        // hmm, phpunit won't mock a traversable interface so mocking the concrete class
        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new PHPCRReferenceType($this->session);
    }

    public function testBuidForm()
    {
        $this->type->buildForm($this->builder, array());
    }
}

