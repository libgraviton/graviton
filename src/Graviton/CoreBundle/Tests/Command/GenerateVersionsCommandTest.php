<?php
/** GenerateVersionsCommandTest **/

namespace Graviton\CoreBundle\Tests\Command;

use Graviton\CoreBundle\Command\GenerateVersionsCommand;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Parser;

/**
 * GenerateVersionsCommandTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GenerateVersionsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the execution of the command
     *
     * @return void
     */
    public function testGenerateVersions()
    {
        $kernel = GravitonTestCase::createKernel();
        $application = new Application($kernel);
        $application->getKernel()->boot();

        /** @var GenerateVersionsCommand $command */
        $command = $application->getKernel()->getContainer()->get('graviton.core.command.generateversions');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $rootDir = $kernel->getContainer()->getParameter("kernel.root_dir");
        $contextDir = $rootDir;
        // are we in Wrapper context?
        if (strstr($contextDir, 'vendor')) {
            $contextDir.='/../../../../app';
        }

        $parser = new Parser();
        $config = $parser->parse(file_get_contents($contextDir.'/config/version_service.yml'));
        $versions = $parser->parse(file_get_contents($rootDir.'/../versions.yml'));

        $deliveredVersions = [];
        foreach ($versions as $version) {
            $deliveredVersions[$version['id']] = $version['version'];
        };

        // check if all keys are in the resulting array (canonical, because the sorting doesnt matter)
        $this->assertEquals(
            $config['desiredVersions'],
            array_keys($deliveredVersions),
            "canonical = true",
            0.0,
            10,
            true
        );

        // check if the version contains at least 4 chars (i.e. minimum dev-)
        foreach ($config['desiredVersions'] as $desiredVersion) {
            if (isset($deliveredVersions[$desiredVersion])) {
                $this->assertTrue(strlen($deliveredVersions[$desiredVersion])>3);
            }
        }
    }
}
