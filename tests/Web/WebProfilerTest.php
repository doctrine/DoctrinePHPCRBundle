<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Web\DataCollector;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

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
        $client = $this->getClient();
        $client->enableProfiler();

        if (\method_exists($client, 'catchExceptions')) {
            // Before Symfony 3.4, the exceptions were not caught
            // TODO: Remove the condition once Symfony 3.3 is no more supported
            $client->catchExceptions(false);
        }

        $client->request('GET', '/phpcr_request');
        $this->assertResponseSuccess($client->getResponse());

        $token = $client->getProfile()->getToken();
        $uri = \str_replace('{token}', $token, $uri);

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
