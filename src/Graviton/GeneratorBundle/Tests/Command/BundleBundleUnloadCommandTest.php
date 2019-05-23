<?php
/**
 * Class BundleBundleUnloadCommandTest
 */

namespace Graviton\GeneratorBundle\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BundleBundleUnloadCommandTest extends TestCase
{

    private $goodBundle;
    private $goodBundleContents;
    private $badBundle;
    private $badBundleContents;
    private $originalEnv;

    /**
     * This method is called before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->goodBundle  = __DIR__.'/resources/bundlebundle/good/GravitonDynBundleBundle.php';
        $this->goodBundleContents = file_get_contents($this->goodBundle);
        $this->badBundle  = __DIR__.'/resources/bundlebundle/invalid/GravitonDynBundleBundle.php';
        $this->badBundleContents = file_get_contents($this->badBundle);
        $this->originalEnv = $_ENV;
    }

    /**
     * This method is called after each test.
     *
     * @return void
     */
    protected function tearDown() : void
    {
        // restore files
        file_put_contents($this->goodBundle, $this->goodBundleContents);
        file_put_contents($this->badBundle, $this->badBundleContents);
        $_ENV = $this->originalEnv;
    }

    /**
     * test normal operation
     *
     * @return void
     */
    public function testExecute()
    {
        $commandTester = $this->getCommandTester();

        $_ENV['DYN_GROUP_DUDE'] = 'dude';
        $_ENV['DYN_HAS_DUDE'] = 'false';
        $_ENV['DYN_GROUP_KAISER'] = 'kaiser';
        $_ENV['DYN_HAS_KAISER'] = 'false';
        $_ENV['DYN_GROUP_FRANZ'] = 'franz';
        // no disabler set, franz should still be there

        $commandTester->execute(['baseDir' => dirname($this->goodBundle)]);
        $contents = file_get_contents($this->goodBundle);

        $this->assertStringContainsString('FranzBundle', $contents);
        $this->assertStringNotContainsString('Dude', $contents);
        $this->assertStringNotContainsString('Kaiser', $contents);
    }

    /**
     * no changes if nothing set
     *
     * @return void
     */
    public function testExecuteNoChanges()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->execute(['baseDir' => dirname($this->goodBundle)]);
        $this->assertEquals($this->goodBundleContents, file_get_contents($this->goodBundle));
    }

    /**
     * no changes if markers not found
     *
     * @return void
     */
    public function testExecuteInvalidBundle()
    {
        $commandTester = $this->getCommandTester();

        $_ENV['DYN_GROUP_DUDE'] = 'SOME';
        $_ENV['DYN_HAS_DUDE'] = 'false';

        $commandTester->execute(['baseDir' => dirname($this->badBundle)]);
        $this->assertEquals($this->badBundleContents, file_get_contents($this->badBundle));
    }

    /**
     * gets the tester
     *
     * @return CommandTester tester
     */
    private function getCommandTester()
    {
        $kernel = $this->getMockBuilder('\Symfony\Component\HttpKernel\KernelInterface')
            ->getMockForAbstractClass();

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMockForAbstractClass();

        $kernel->method('getBundles')->willReturn([]);
        $kernel->method('getContainer')->willReturn($container);

        // mock the Kernel or create one depending on your needs
        $application = new Application($kernel);
        $application->add(new BundeBundleUnloadCommand());

        $command = $application->find('graviton:generate:bundlebundleunload');
        return new CommandTester($command);
    }
}
