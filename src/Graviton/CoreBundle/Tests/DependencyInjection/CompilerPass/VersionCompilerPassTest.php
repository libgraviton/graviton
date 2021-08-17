<?php
/**
 * VersionCompilerPassTest class file
 */

namespace Graviton\CoreBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\CoreBundle\Compiler\VersionCompilerPass;
use Jean85\PrettyVersions;
use Jean85\Version;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersionCompilerPassTest extends \PHPUnit\Framework\TestCase
{

    /**
     * test that our compiler pass sets information correctly
     *
     * @return void
     */
    public function testVersionInformationSetting()
    {
        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('getParameter')
            ->with('kernel.root_dir')
            ->willReturn(__DIR__.'/Resources/version');

        // what we expect to be set
        $containerDouble
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                [
                    'graviton.core.version.data',
                    [
                        'self' => 'v20.7.0',
                        'symfony/symfony' => 'v1.0.1',
                        'php' => PHP_VERSION
                    ]
                ],
                [
                    'graviton.core.version.header',
                    'self: v20.7.0; symfony/symfony: v1.0.1;'
                ]
            );

        $prettyVersion = new class extends PrettyVersions {
            /**
             * get version
             *
             * @param string $packageName package name
             *
             * @return Version version
             */
            public static function getVersion(string $packageName): Version
            {
                if ($packageName == 'graviton/graviton') {
                    return new Version($packageName, 'v20.7.0@ssfdf');
                }

                return new Version($packageName, 'v1.0.1@ssfdf');
            }
        };

        $sut = new VersionCompilerPass($prettyVersion);
        $sut->process($containerDouble);
    }
}
