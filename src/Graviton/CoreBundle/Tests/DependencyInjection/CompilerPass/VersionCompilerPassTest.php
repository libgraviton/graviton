<?php
/**
 * VersionCompilerPassTest class file
 */

namespace Graviton\CoreBundle\Tests\DependencyInjection\CompilerPass;

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
            ->expects($this->at(1))
            ->method('setParameter')
            ->with(
                'graviton.core.version.data',
                [
                    'self' => 'v20.7.0',
                    'symfony/symfony' => 'v1.0.1',
                    'php' => PHP_VERSION
                ]
            );
        $containerDouble
            ->expects($this->at(2))
            ->method('setParameter')
            ->with(
                'graviton.core.version.header',
                'self: v20.7.0; symfony/symfony: v1.0.1;'
            );


        $sutDouble = $this->getMockBuilder('Graviton\CoreBundle\Compiler\VersionCompilerPass')
            ->disableOriginalConstructor()
            ->setMethods(['getPackageVersion'])
            ->getMock();
        $sutDouble
            ->expects($this->at(0))
            ->method('getPackageVersion')
            ->with('graviton/graviton')
            ->willReturn('v20.7.0');
        $sutDouble
            ->expects($this->at(1))
            ->method('getPackageVersion')
            ->with('symfony/symfony')
            ->willReturn('v1.0.1');

        $sutDouble->process($containerDouble);
    }
}
