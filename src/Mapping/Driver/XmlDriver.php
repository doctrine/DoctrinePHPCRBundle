<?php

namespace Doctrine\Bundle\PHPCRBundle\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver as BaseXmlDriver;

/**
 * XmlDriver that additionally looks for mapping information in a global file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class XmlDriver extends BaseXmlDriver
{
    const DEFAULT_FILE_EXTENSION = '.phpcr.xml';

    /**
     * {@inheritdoc}
     */
    public function __construct($prefixes, $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        $locator = new SymfonyFileLocator((array) $prefixes, $fileExtension);
        parent::__construct($locator, $fileExtension);
    }
}
