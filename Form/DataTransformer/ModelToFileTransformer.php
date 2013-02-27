<?php

namespace Doctrine\Bundle\PHPCRBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ODM\PHPCR\Document\File;
use Doctrine\ODM\PHPCR\Document\Image;

class ModelToFileTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($uploadedFile)
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return $uploadedFile;
        }

        /** @var $uploadedFile UploadedFile */
        $fileObj = new File();
        $fileObj->setFileContentFromFilesystem($uploadedFile->getPathname());
        $image = new Image();
        $image->setFile($fileObj);
        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($image)
    {
        return $image;
    }
}