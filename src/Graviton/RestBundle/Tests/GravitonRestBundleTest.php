<?php
/**
 * test rest-bundle
 */

namespace Graviton\RestBundle\Tests;

use Graviton\RestBundle\GravitonRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use FOS\RestBundle\FOSRestBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;

/**
 * GravitonMessagingBundleTest
 * 
 * @category Tests
 * @package  GravitonMessagingBundle
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class GravitonBundleRestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check for implemented interfaces
     *
     * @return void
     */
    public function testIsInterface()
    {
        $this->assertInstanceOf(
            '\Graviton\BundleBundle\GravitonBundleInterface',
            new GravitonRestBundle()
        );
    }
    /**
     * test getBundles method
     *
     * @return void
     */
    public function testGetBundles()
    {
        $sut = new GravitonRestBundle();
        $expectation = array(
            new JMSSerializerBundle(),
            new FOSRestBundle(),
            new MisdGuzzleBundle(),
        );

        $result = $sut->getBundles();
        $this->assertEquals($expectation, $result);
    }
}
