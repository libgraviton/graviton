<?php
/**
 * Verifications for the Security bundle extension class
 */
namespace Graviton\SecurityBundle\Tests\DepedencyInjection;

use Graviton\SecurityBundle\DependencyInjection\GravitonSecurityExtension;
use Graviton\SecurityBundle\GravitonSecurityBundle;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonSecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verifies the correct behavior of getConfigDir()
     *
     * @return void
     */
    public function testGetConfigDir()
    {
        $bundle = new GravitonSecurityExtension();

        $this->assertContains(
            '/../Resources/config',
            $bundle->getConfigDir()
        );
    }
}
