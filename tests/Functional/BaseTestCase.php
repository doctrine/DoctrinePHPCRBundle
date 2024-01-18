<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional;

use Doctrine\Bundle\PHPCRBundle\Test\RepositoryManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseTestCase extends WebTestCase
{
    protected function getRepositoryManager(): RepositoryManager
    {
        if (!self::$kernel) {
            self::bootKernel();
        }
        if (!self::$kernel->getContainer()) {
            self::$kernel->boot();
        }
        $container = self::getTestContainer();

        return new RepositoryManager($container->get('doctrine_phpcr'), $container->get('doctrine_phpcr.initializer_manager'));
    }

    protected function assertResponseSuccess(Response $response): void
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($response->getContent());

        $xpath = new \DOMXPath($dom);
        $result = $xpath->query('//div[contains(@class,"text-exception")]/h1');
        $exception = null;
        if ($result->length) {
            $exception = $result->item(0)->nodeValue;
        }

        $this->assertEquals(200, $response->getStatusCode(), $exception ? 'Exception: "'.$exception.'"' : '');
    }

    protected static function getTestContainer(): ContainerInterface
    {
        if (!self::$kernel) {
            self::bootKernel();
        }
        if (!self::$kernel->getContainer()) {
            self::$kernel->boot();
        }

        return self::getContainer();
    }
}
