<?php
/**
 * test rest-bundle
 */

namespace Graviton\RestBundle\Tests;

use Graviton\RestBundle\GravitonRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;

/**
 * GravitonMessagingBundleTest
 *
 * @category Tests
 * @package  GravitonMessagingBundle
 * @link     http://swisscom.com
 */
class GravitonRestBundleTest extends \PHPUnit_Framework_TestCase
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
            new MisdGuzzleBundle(),
            new KnpPaginatorBundle(),
        );

        $result = $sut->getBundles();
        $this->assertEquals($expectation, $result);
    }
}
