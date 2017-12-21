<?php


namespace Doctrine\Bundle\PHPCRBundle\Mapping\Driver;

use Doctrine\ODM\PHPCR\Mapping\Driver\YamlDriver as BaseYamlDriver;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;

/**
 * YamlDriver that additionally looks for mapping information in a global file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class YamlDriver extends BaseYamlDriver
{
    const DEFAULT_FILE_EXTENSION = '.phpcr.yml';

    /**
     * {@inheritdoc}
     */
    public function __construct($prefixes, $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        $locator = new SymfonyFileLocator((array) $prefixes, $fileExtension);
        parent::__construct($locator, $fileExtension);
    }
}
