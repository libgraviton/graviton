<?php
/**
 * Verifications for the Security bundle extension class
 */
namespace Graviton\SecurityBundle\Tests\DepedencyInjection;

use Graviton\SecurityBundle\DependencyInjection\GravitonSecurityExtension;
use PHPUnit\Framework\TestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonSecurityExtensionTest extends TestCase
{
    /**
     * Verifies the correct behavior of getConfigDir()
     *
     * @return void
     */
    public function testGetConfigDir()
    {
        $bundle = new GravitonSecurityExtension();

        $this->assertStringContainsString(
            '/../Resources/config',
            $bundle->getConfigDir()
        );
    }
}
