<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\FileStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * check if loading from a single file works
     *
     * @return void
     */
    public function testLoadReturnsSingleFileArray()
    {
        $file = __DIR__.'/resources/definition/test1.json';

        $sut = new FileStrategy();

        $this->assertTrue($sut->supports($file));
        $this->assertEquals([file_get_contents($file)], $sut->load($file));
    }
}
