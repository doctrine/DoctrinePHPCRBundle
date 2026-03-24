<?php

declare(strict_types=1);

namespace Doctrine\Bundle\PHPCRBundle\Mapping\Driver;

use Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver as BaseXmlDriver;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;

/**
 * XmlDriver that additionally looks for mapping information in a global file.
 */
final class SimplifiedXmlDriver extends BaseXmlDriver
{
    public function __construct($prefixes, $fileExtension = BaseXmlDriver::DEFAULT_FILE_EXTENSION)
    {
        $locator = new SymfonyFileLocator((array) $prefixes, $fileExtension);

        parent::__construct($locator, $fileExtension);
    }
}
