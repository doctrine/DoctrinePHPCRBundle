<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

/**
 * @PHPCR\Document()
 */
class ReferrerDocument
{
    /**
     * @PHPCR\Id(strategy="assigned")
     */
    public $id;

    /**
     * @PHPCR\ReferenceOne()
     */
    protected $single;

    /**
     * @PHPCR\ReferenceMany()
     */
    protected $documents;

    /**
     * @PHPCR\ReferenceOne(targetDocument="Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument")
     */
    protected $testDocument;

    /**
     * @PHPCR\ReferenceMany(targetDocument="Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument")
     */
    protected $testDocuments;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->testDocuments = new ArrayCollection();
    }

    public function addDocument($doc)
    {
        $this->documents->add($doc);
    }

    /**
     * @return mixed
     */
    public function getSingle()
    {
        return $this->single;
    }

    /**
     * @return mixed
     */
    public function getTestDocument()
    {
        return $this->testDocument;
    }

    /**
     * @return mixed
     */
    public function getTestDocuments()
    {
        return $this->testDocuments;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Either define __toString or set property attribute on form mapping.
     */
    public function __toString()
    {
        return $this->id;
    }
}
