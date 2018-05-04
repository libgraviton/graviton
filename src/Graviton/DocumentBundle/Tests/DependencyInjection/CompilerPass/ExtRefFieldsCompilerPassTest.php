<?php
/**
 * ExtRefFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\ExtRefFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class ExtRefFieldsCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $serviceDouble = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->setMethods(['getTag'])
            ->getMock();
        $serviceDouble
            ->expects($this->once())
            ->method('getTag')
            ->with('graviton.rest')
            ->willReturn([]);

        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/extref')
                ->name('*.mongodb.yml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/extref')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/schema')
                ->name('*.json')
        );

        $containerDouble = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('graviton.document.map'))
            ->willReturn($documentMap);
        $containerDouble
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('graviton.rest')
            ->willReturn(['gravitonTest.document.controller.A' => []]);
        $containerDouble
            ->expects($this->once())
            ->method('getDefinition')
            ->with('gravitonTest.document.controller.A')
            ->willReturn($serviceDouble);
        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.extref.fields'),
                [
                    'gravitontest.document.rest.a.get' => [
                        '$exposedRefA',

                        'achild.$exposedRefB',
                        'achild.bchild.$exposedRefC',
                        'achild.bchildren.0.$exposedRefC',

                        'achildren.0.$exposedRefB',
                        'achildren.0.bchild.$exposedRefC',
                        'achildren.0.bchildren.0.$exposedRefC',
                    ],
                    'gravitontest.document.rest.a.all' => [
                        '$exposedRefA',

                        'achild.$exposedRefB',
                        'achild.bchild.$exposedRefC',
                        'achild.bchildren.0.$exposedRefC',

                        'achildren.0.$exposedRefB',
                        'achildren.0.bchild.$exposedRefC',
                        'achildren.0.bchildren.0.$exposedRefC',
                    ],
                ]
            );

        $compilerPass = new ExtRefFieldsCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
