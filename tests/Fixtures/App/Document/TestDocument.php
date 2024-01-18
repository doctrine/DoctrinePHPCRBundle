<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\Mapping\Attributes as PHPCR;

#[PHPCR\Document(referenceable: true)]
class TestDocument
{
    #[PHPCR\Id]
    public string $id;

    #[PHPCR\ParentDocument]
    public $parent;

    #[PHPCR\Nodename]
    public string $nodename;

    #[PHPCR\Uuid]
    public string $uuid;

    #[PHPCR\Child]
    public $child;

    #[PHPCR\Children]
    protected Collection $children;

    #[PHPCR\Referrers(referencedBy: 'documents', referringDocument: ReferrerDocument::class)]
    protected Collection $referrers;

    #[PHPCR\MixedReferrers]
    protected Collection $mixedReferrers;

    #[PHPCR\Field(type: 'boolean')]
    public bool $bool;

    #[PHPCR\Field(type: 'date')]
    public \DateTimeInterface $date;

    #[PHPCR\Field(type: 'string')]
    public string $text;

    #[PHPCR\Field(type: 'double')]
    public float $number;

    #[PHPCR\Field(type: 'long')]
    public int $long;

    #[PHPCR\Field(type: 'int')]
    public int $integer;

    #[PHPCR\Field(type: 'boolean', multivalue: true, nullable: true)]
    public ?array $mbool;

    #[PHPCR\Field(type: 'date', multivalue: true, nullable: true)]
    public ?array $mdate;

    #[PHPCR\Field(type: 'string', multivalue: true, nullable: true)]
    public ?array $mtext;

    #[PHPCR\Field(type: 'double', multivalue: true, nullable: true)]
    public ?array $mnumber;

    #[PHPCR\Field(type: 'long', multivalue: true, nullable: true)]
    public ?array $mlong;

    #[PHPCR\Field(type: 'int', multivalue: true, nullable: true)]
    public ?array $minteger;

    public function __construct()
    {
        $this->referrers = new ArrayCollection();
        $this->mixedReferrers = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getReferrers(): Collection
    {
        return $this->referrers;
    }

    public function addReferrer($referrer): void
    {
        $this->referrers->add($referrer);
    }

    public function removeReferrer($referrer): void
    {
        $this->referrers->remove($referrer);
    }

    public function getMixedReferrers(): Collection
    {
        return $this->mixedReferrers;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild($referrer): void
    {
        $this->children->add($referrer);
    }

    public function removeChild($referrer): void
    {
        $this->children->remove($referrer);
    }

    /**
     * Either define __toString or set property attribute on form mapping.
     */
    public function __toString(): string
    {
        return $this->id;
    }
}
