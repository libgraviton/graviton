<?php
/**
 * RqlFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\RqlFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class RqlFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $expectedReqult = [
            'id'                            => 'id',
            'key'                           => 'key',
            'ref'                           => '$exposedRefA',

            'achild'                        => 'achild',
            'achild.id'                     => 'achild.id',
            'achild.key'                    => 'achild.key',
            'achild.ref'                    => 'achild.$exposedRefB',
            'achild.bchild'                 => 'achild.bchild',
            'achild.bchild.id'              => 'achild.bchild.id',
            'achild.bchild.key'             => 'achild.bchild.key',
            'achild.bchild.ref'             => 'achild.bchild.$exposedRefC',
            'achild.bchildren'              => 'achild.bchildren',
            'achild.bchildren.0'            => 'achild.bchildren.0',
            'achild.bchildren.0.id'         => 'achild.bchildren.0.id',
            'achild.bchildren.0.key'        => 'achild.bchildren.0.key',
            'achild.bchildren.0.ref'        => 'achild.bchildren.0.$exposedRefC',

            'achildren'                     => 'achildren',
            'achildren.0'                   => 'achildren.0',
            'achildren.0.id'                => 'achildren.0.id',
            'achildren.0.key'               => 'achildren.0.key',
            'achildren.0.ref'               => 'achildren.0.$exposedRefB',
            'achildren.0.bchild'            => 'achildren.0.bchild',
            'achildren.0.bchild.id'         => 'achildren.0.bchild.id',
            'achildren.0.bchild.key'        => 'achildren.0.bchild.key',
            'achildren.0.bchild.ref'        => 'achildren.0.bchild.$exposedRefC',
            'achildren.0.bchildren'         => 'achildren.0.bchildren',
            'achildren.0.bchildren.0'       => 'achildren.0.bchildren.0',
            'achildren.0.bchildren.0.id'    => 'achildren.0.bchildren.0.id',
            'achildren.0.bchildren.0.key'   => 'achildren.0.bchildren.0.key',
            'achildren.0.bchildren.0.ref'   => 'achildren.0.bchildren.0.$exposedRefC',
        ];

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
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/extref')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/validation/extref')
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
                $this->equalTo('graviton.document.rql.fields'),
                [
                    'gravitontest.document.rest.a.get' => $expectedReqult,
                    'gravitontest.document.rest.a.all' => $expectedReqult,
                ]
            );

        $compilerPass = new RqlFieldsCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
