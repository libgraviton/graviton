<?php
/**
 * ExtRefFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Symfony\Component\Finder\Finder;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class ExtRefFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
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

        $containerDouble = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('getDefinition')
            ->with('gravitonTest.document.controller.A')
            ->willReturn($serviceDouble);
        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.type.extref.fields'),
                [
                    'gravitontest.document.rest.a.get' => [
                        '$aref',

                        'achild.$bref',
                        'achild.bchild.$cref',
                        'achild.bchildren.0.$cref',

                        'achildren.0.$bref',
                        'achildren.0.bchild.$cref',
                        'achildren.0.bchildren.0.$cref',
                    ],
                    'gravitontest.document.rest.a.all' => [
                        '$aref',

                        'achild.$bref',
                        'achild.bchild.$cref',
                        'achild.bchildren.0.$cref',

                        'achildren.0.$bref',
                        'achildren.0.bchild.$cref',
                        'achildren.0.bchildren.0.$cref',
                    ],
                ]
            );

        $compilerPass = $this
            ->getMockBuilder('Graviton\\DocumentBundle\\DependencyInjection\\Compiler\\ExtRefFieldsCompilerPass')
            ->setMethods(['getDoctrineMappingFinder'])
            ->getMock();
        $compilerPass
            ->expects($this->any())
            ->method('getDoctrineMappingFinder')
            ->willReturn(
                (new Finder())
                    ->in(__DIR__.'/Resources/doctrine')
                    ->name('*.mongodb.xml')
            );

        $compilerPass->processServices($containerDouble, ['gravitonTest.document.controller.A']);
    }
}
