<?php
/**
 * ExtRefFieldsCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\Annotation\ClassScanner;
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
        $documentMap = new DocumentMap(
            ClassScanner::getDocumentAnnotationDriver([__DIR__.'/Resources/Document/Extref']),
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
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.extref.fields'),
                [
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\A' => [
                        '$exposedRefA',

                        'achild.$exposedRefB',
                        'achild.bchild.$exposedRefC',
                        'achild.bchildren.0.$exposedRefC',

                        'achildren.0.$exposedRefB',
                        'achildren.0.bchild.$exposedRefC',
                        'achildren.0.bchildren.0.$exposedRefC',
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\B' => [
                        '$exposedRefB',

                        'bchild.$exposedRefC',
                        'bchildren.0.$exposedRefC'
                    ],
                    'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\Extref\C' => [
                        '$exposedRefC'
                    ]
                ]
            );

        $compilerPass = new ExtRefFieldsCompilerPass();
        $compilerPass->process($containerDouble);
    }
}
