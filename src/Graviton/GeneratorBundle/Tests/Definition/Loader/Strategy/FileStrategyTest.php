<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\FileStrategy;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadReturnsSingleFileArray()
    {
        $file = __DIR__.'/test.json';
        $jsonDef = array(
            new JsonDefinition($file)
        );

        $sut = new FileStrategy;
        $this->assertTrue($sut->accepts($file));
        $this->assertEquals($jsonDef, $sut->load($file));
    }
}
