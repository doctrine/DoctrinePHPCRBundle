<?php

namespace DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension;

class DoctrinePHPCRExtensionTest extends AbstractExtensionTestCase
{
    public function getContainerExtensions()
    {
        return array(
            new DoctrinePHPCRExtension()
        );
    }

    public function testLoad()
    {
        // Smoke test for DI extension
        $this->load();
    }
}
