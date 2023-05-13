<?php

namespace Doctrine\Bundle\PHPCRBundle\Mapping\Driver;

use Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver as BaseXmlDriver;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;

/**
 * XmlDriver that additionally looks for mapping information in a global file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class XmlDriver extends BaseXmlDriver
{
    public const DEFAULT_FILE_EXTENSION = '.phpcr.xml';

    public function __construct(array $prefixes, string $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        $locator = new SymfonyFileLocator($prefixes, $fileExtension);
        parent::__construct($locator, $fileExtension);
    }
}
