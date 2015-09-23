<?php
/**
 * SwaggerStrategyTest
 */

namespace Graviton\ProxyBundle\Tests\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\SwaggerStrategy;

/**
 * tests for the SwaggerStrategy class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SwaggerStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwaggerStrategy
     */
    private $sut;

    /**
     * @var /stdClass
     */
    private $swagger;

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sut = new SwaggerStrategy();

        $this->swagger = new \stdClass();
        $this->swagger->swagger = "2.0";
        $this->swagger->paths = new \stdClass();
        $this->swagger->definition = new \stdClass();
        $this->swagger->info = new \stdClass();
        $this->swagger->info->title = "test swagger";
        $this->swagger->info->version = "1.0.0";
    }


    /**
     * test the supports method
     *
     * @return void
     */
    public function testSupported()
    {
        $this->assertTrue($this->sut->supports(json_encode($this->swagger)));
    }

    /**
     * test the supports method, when JSON not supported
     *
     * @return void
     */
    public function testNotSupported()
    {
        $notValidJson = clone $this->swagger;
        unset($notValidJson->paths);
        unset($notValidJson->info->title);
        $this->assertFalse($this->sut->supports(json_encode($notValidJson)));
    }

    /**
     * test missing fallback data
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Missing mandatory key (host) in fallback data set.
     *
     * @return void
     */
    public function testMissingFallbackData()
    {
        $this->sut->process('{}', array());
    }

    /**
     * test the process method
     *
     * @return void
     */
    public function testProcess()
    {
        $fallbackData = array('host' => 'localhost');
        $apiDefinition = $this->sut->process(json_encode($this->swagger), $fallbackData);
        $this->assertInstanceOf('Graviton\ProxyBundle\Definition\ApiDefinition', $apiDefinition);
    }
}
