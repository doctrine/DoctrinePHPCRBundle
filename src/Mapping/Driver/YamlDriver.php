<?php

namespace Doctrine\Bundle\PHPCRBundle\Mapping\Driver;

use Doctrine\ODM\PHPCR\Mapping\Driver\YamlDriver as BaseYamlDriver;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;

/**
 * YamlDriver that additionally looks for mapping information in a global file.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class YamlDriver extends BaseYamlDriver
{
    public const DEFAULT_FILE_EXTENSION = '.phpcr.yml';

    public function __construct(array $prefixes, string $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        $locator = new SymfonyFileLocator($prefixes, $fileExtension);
        parent::__construct($locator, $fileExtension);
    }
}
