<?php
/**
 * VersionCompilerPassTest class file
 */

namespace Graviton\CoreBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\CommonBundle\Component\Deployment\VersionInformation;
use Graviton\CoreBundle\Compiler\VersionCompilerPass;

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
            ->with('kernel.project_dir')
            ->willReturn(__DIR__ . '/Resources/version');

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

        $prettyVersion = new class extends VersionInformation {

            /**
             * get version
             *
             * @param string $packageName package name
             *
             * @return Version version
             */
            public function getPrettyVersion($packageName) : ?string
            {
                if ($packageName == 'graviton/graviton') {
                    return 'v20.7.0';
                }

                return 'v1.0.1';
            }
        };

        $sut = new VersionCompilerPass($prettyVersion);
        $sut->process($containerDouble);
    }
}
