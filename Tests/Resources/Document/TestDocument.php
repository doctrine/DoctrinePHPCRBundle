<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @PHPCR\Document(referenceable=true)
 */
class TestDocument
{
    /**
     * @PHPCR\Id()
     */
    public $id;

    /**
     * @PHPCR\ParentDocument()
     */
    public $parent;

    /**
     * @PHPCR\Nodename()
     */
    public $nodename;

    /**
     * @PHPCR\Uuid
     */
    public $uuid;

    /**
     * @PHPCR\Child()
     */
    public $child;

    /**
     * @PHPCR\Children()
     */
    protected $children;

    /**
     * @PHPCR\Referrers(
     *     referringDocument="Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument",
     *     referencedBy="documents"
     * )
     */
    protected $referrers;

    /**
     * @PHPCR\MixedReferrers()
     */
    protected $mixedReferrers;


    /**
     * @PHPCR\Boolean()
     */
    public $bool;

    /**
     * @PHPCR\Date()
     */
    public $date;

    /**
     * @PHPCR\String()
     */
    public $text;

    /**
     * @PHPCR\Double()
     */
    public $number;

    /**
     * @PHPCR\Long()
     */
    public $long;

    /**
     * @PHPCR\Int()
     */
    public $integer;


    /**
     * @PHPCR\Boolean(multivalue=true, nullable=true)
     */
    public $mbool;

    /**
     * @PHPCR\Date(multivalue=true, nullable=true)
     */
    public $mdate;

    /**
     * @PHPCR\String(multivalue=true, nullable=true)
     */
    public $mtext;

    /**
     * @PHPCR\Double(multivalue=true, nullable=true)
     */
    public $mnumber;

    /**
     * @PHPCR\Long(multivalue=true, nullable=true)
     */
    public $mlong;

    /**
     * @PHPCR\Int(multivalue=true, nullable=true)
     */
    public $minteger;

    public function __construct()
    {
        $this->referrers = new ArrayCollection();
        $this->mixedReferrers = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getReferrers()
    {
        return $this->referrers;
    }

    public function addReferrer($referrer)
    {
        $this->referrers->add($referrer);
    }

    public function removeReferrer($referrer)
    {
        $this->referrers->remove($referrer);
    }

    public function getMixedReferrers()
    {
        return $this->mixedReferrers;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild($referrer)
    {
        $this->children->add($referrer);
    }

    public function removeChild($referrer)
    {
        $this->children->remove($referrer);
    }

    /**
     * Either define __toString or set property attribute on form mapping
     */
    public function __toString()
    {
        return $this->id;
    }
}
