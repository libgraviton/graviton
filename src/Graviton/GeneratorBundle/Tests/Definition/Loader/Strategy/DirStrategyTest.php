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
        $jsonDef = array(
            new JsonDefinition($dir.'/test1.json'),
            new JsonDefinition($dir.'/test2.json'),
        );

        $sut = new DirStrategy;
        $this->assertTrue($sut->accepts($dir));
        $this->assertEquals($jsonDef, $sut->load($dir));
    }
}
