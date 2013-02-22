<?php

namespace Doctrine\Bundle\PHPCRBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Image
{

    /**
     * @PHPCRODM\Id
     */
    protected $path;

    /**
     * Image file child
     *
     * @PHPCRODM\Child(name="file", cascade="persist")
     */
    protected $file;


    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setMimeType($mimeType)
    {
        $this->file->getContent()->setMimeType($mimeType);
    }

    public function getMimeType()
    {
        return $this->file->getContent()->getMimeType();
    }

    public function getContent()
    {
        return $this->file->getFileContentAsStream();
    }

    public function __toString()
    {
        return $this->path;
    }

}
