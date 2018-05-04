<?php
/**
 * Verifications for the Security bundle extension class
 */
namespace Graviton\SecurityBundle\Tests\DepedencyInjection;

use Graviton\SecurityBundle\DependencyInjection\GravitonSecurityExtension;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonSecurityExtensionTest extends \PHPUnit\Framework\TestCase
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
