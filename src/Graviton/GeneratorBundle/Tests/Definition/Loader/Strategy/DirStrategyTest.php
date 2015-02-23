<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\DirStrategy;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDir()
    {
        $dir = __DIR__.'/dir';

        $sut = new DirStrategy;
        $this->assertTrue($sut->supports($dir));
        $data = $sut->load($dir);
        $this->assertContainsOnlyInstancesOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $data);
        $this->assertCount(2, $data);
    }
}
