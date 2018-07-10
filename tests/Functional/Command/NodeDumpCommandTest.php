<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Command;

use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NodeDumpCommandTest extends BaseTestCase
{
    public function setUp()
    {
        $repositoryManager = $this->getRepositoryManager();
        $repositoryManager->loadFixtures([LoadData::class]);
    }

    protected function getKernel()
    {
        if (!self::$kernel) {
            self::bootKernel();
        }
        if (!self::$kernel->getContainer()) {
            self::$kernel->boot();
        }

        return self::$kernel;
    }

    public function testMaxLineLengthOptionIsAppliedSuccessfully()
    {
        $application = new Application($this->getKernel());

        $command = $application->find('doctrine:phpcr:node:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--props' => true,
            '--max_line_length' => 120,
             'identifier' => '/test/doc-very-long',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertRegExp('/^\s+ - \s+ text \s+ = \s+ .{120} \.\.\.$/mx', $output);

        $commandTester->execute([
            'command' => $command->getName(),
            '--props' => true,
            '--max_line_length' => 20,
            'identifier' => '/test/doc-very-long',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertRegExp('/^\s+ - \s+ text \s+ = \s+ .{20} \.\.\.$/mx', $output);
    }
}
