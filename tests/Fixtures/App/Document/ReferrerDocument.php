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
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\Mapping\Attributes as PHPCR;

#[PHPCR\Document]
class ReferrerDocument
{
    #[PHPCR\Id(strategy: 'assigned')]
    public string $id;

    #[PHPCR\ReferenceOne]
    protected $single;

    #[PHPCR\ReferenceMany]
    protected Collection $documents;

    #[PHPCR\ReferenceOne(targetDocument: TestDocument::class)]
    protected $testDocument;

    #[PHPCR\ReferenceMany(targetDocument: TestDocument::class)]
    protected Collection $testDocuments;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->testDocuments = new ArrayCollection();
    }

    public function addDocument($doc): void
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
     * @return Collection<object>
     */
    public function getTestDocuments(): Collection
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
    public function __toString(): string
    {
        return $this->id;
    }
}
