<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Functional\Command;

use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR\LoadData;
use Doctrine\Bundle\PHPCRBundle\Tests\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NodeDumpCommandTest extends BaseTestCase
{
    public function setUp(): void
    {
        self::bootKernel();

        $repositoryManager = $this->getRepositoryManager();
        $repositoryManager->loadFixtures([LoadData::class]);
    }

    public function testMaxLineLengthOptionIsAppliedSuccessfully(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find('doctrine:phpcr:node:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--props' => true,
            '--max_line_length' => 120,
             'identifier' => '/test/doc-very-long',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/^\s+ - \s+ text \s+ = \s+ .{120} \.\.\.$/mx', $output);

        $commandTester->execute([
            'command' => $command->getName(),
            '--props' => true,
            '--max_line_length' => 20,
            'identifier' => '/test/doc-very-long',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/^\s+ - \s+ text \s+ = \s+ .{20} \.\.\.$/mx', $output);
    }
}
