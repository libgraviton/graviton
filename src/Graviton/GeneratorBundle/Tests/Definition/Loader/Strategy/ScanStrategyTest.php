<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\ScanStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ScanStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check loading with null input
     *
     * @return void
     */
    public function testLoadDir()
    {
        $dir = __DIR__;

        $sut = new ScanStrategy();
        $sut->setScanDir($dir);

        $this->assertTrue($sut->supports(null));

        $loadedFiles = $sut->load($dir);

        /**
         * we want to know that we have 2 items and that it contains both the things we expect ;-)
         */
        $this->assertCount(2, $loadedFiles);
        $this->assertContains(file_get_contents($dir.'/resources/definition/test1.json'), $loadedFiles);
        $this->assertContains(file_get_contents($dir.'/resources/definition/test2.json'), $loadedFiles);
    }
}
