<?php
/** GenerateBuildIndexesCommandTest **/

namespace Graviton\GeneratorBundle\Tests\Command;

use Graviton\GeneratorBundle\Command\GenerateBuildIndexesCommand;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * GenerateBuildIndexesCommandTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateBuildIndexesCommandTest extends GravitonTestCase
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $documentManager;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->documentManager = static::$kernel->getContainer()
            ->get('doctrine_mongodb.odm.default_document_manager');
    }

    /**
     * Tests the execution of the command building all indexes
     *
     * @return void
     */
    public function testExecute()
    {
        $kernel = self::createKernel();

        $application = new Application($kernel);
        $application->add(new GenerateBuildIndexesCommand($this->documentManager));

        $command = $application->find('graviton:generate:build-indexes');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        // test the creation of the index searchModuleIndex
        $indexInfo = $this->documentManager
            ->getDocumentCollection("GravitonDyn\ModuleBundle\Document\Module")
            ->getIndexInfo();

        $this->assertEquals('searchModuleIndex', $indexInfo[1]['name']);
    }
}
