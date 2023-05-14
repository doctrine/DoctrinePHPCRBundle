<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Web;

use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;

/**
 * Tests the Data Collector by running the Web Profiler.
 */
class WebProfilerTest extends BaseTestCase
{
    /**
     * @dataProvider provideWebProfilerUris
     */
    public function testRun(string $uri): void
    {
        $client = self::createClient();
        $client->enableProfiler();

        $client->request('GET', '/phpcr_request');
        $this->assertResponseSuccess($client->getResponse());

        $token = $client->getProfile()->getToken();
        $uri = str_replace('{token}', $token, $uri);

        $client->request('GET', $uri);
        $this->assertResponseSuccess($client->getResponse());
    }

    public function provideWebProfilerUris(): array
    {
        return [
            'the default panel' => ['/_profiler/{token}'],
            'the PHPCR panel' => ['/_profiler/{token}?panel=phpcr'],
        ];
    }
}
