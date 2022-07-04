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

        return new RepositoryManager(self::getTestContainer());
    }

    protected function assertResponseSuccess(Response $response)
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

        if (method_exists(self::class, 'getContainer')) {
            return self::getContainer();
        }

        return self::$container;
    }
}
