<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Web\DataCollector;

use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;

/**
 * Tests the Data Collector by running the Web Profiler.
 *
 * @testdox The web profiler
 */
class WebProfilerTest extends BaseTestCase
{
    /**
     * @testdox should run
     *
     * @dataProvider provideWebProfilerUris
     */
    public function testRun(string $uri)
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

    public function provideWebProfilerUris()
    {
        return [
            'the default panel' => ['/_profiler/{token}'],
            'the PHPCR panel' => ['/_profiler/{token}?panel=phpcr'],
        ];
    }
}
