<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\DirStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test loading multiple files from dir
     *
     * @return void
     */
    public function testLoadDir()
    {
        $dir = __DIR__.'/resources/definition';

        $sut = new DirStrategy();
        $this->assertTrue($sut->supports($dir));

        $loadedFiles = $sut->load($dir);

        $this->assertCount(2, $loadedFiles);
        $this->assertContains(file_get_contents($dir.'/test1.json'), $loadedFiles);
        $this->assertContains(file_get_contents($dir.'/test2.json'), $loadedFiles);
    }
}
