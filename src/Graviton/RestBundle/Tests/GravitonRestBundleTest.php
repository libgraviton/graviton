<?php
/**
 * test rest-bundle
 */

namespace Graviton\RestBundle\Tests;

use Graviton\RestBundle\GravitonRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Misd\GuzzleBundle\MisdGuzzleBundle;

/**
 * GravitonMessagingBundleTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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

        $result = $sut->getBundles();

        $this->assertContains(new JMSSerializerBundle(), $result, '', false, false);
        $this->assertContains(new MisdGuzzleBundle(), $result, '', false, false);
        $this->assertContains(new KnpPaginatorBundle(), $result, '', false, false);
    }

    /**
     * Verifies the correct behavior of build()
     *
     * @return void
     */
    public function testBuild()
    {
        $containerDouble = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('addCompilerPass'))
            ->getMock();
        $containerDouble
            ->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->withConsecutive(
                $this->isInstanceOf('\Graviton\RestBundle\DependencyInjection\Compiler\RestServicesCompilerPass'),
                $this->isInstanceOf('\Graviton\RestBundle\DependencyInjection\Compiler\ReferencehandlerCompilerPass')
            );

        $bundle = new GravitonRestBundle();
        $bundle->build($containerDouble);
    }
}
