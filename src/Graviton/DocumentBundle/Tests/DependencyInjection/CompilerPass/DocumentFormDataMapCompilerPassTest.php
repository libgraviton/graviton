<?php
/**
 * DocumentFormDataMapCompilerPassTest class file
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormDataMapCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormDataMapCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testProcess()
    {
        $baseNamespace = 'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document';

        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                'graviton.document.form.data.map',
                [
                    'stdclass' => [],
                    $baseNamespace.'\A' => [
                        '$integerA'                         => 'integerA',
                        '$titleA'                           => 'titleA',
                        '$extrefA'                          => 'extrefA',
                        '$booleanA'                         => 'booleanA',
                        '$datetimeA'                        => 'datetimeA',
                        '$floatA'                           => 'floatA',
                        '$unstructA'                        => 'unstructA',
                        '$achild'                           => 'achild',
                        '$achildren'                        => 'achildren',

                        '$achild.$fieldB'                   => 'fieldB',
                        '$achild.$bchild'                   => 'bchild',
                        '$achild.$bchild.$fieldC'           => 'fieldC',
                        '$achild.$bchildren'                => 'bchildren',
                        '$achild.$bchildren.0.$fieldC'      => 'fieldC',

                        '$achildren.0.$fieldB'              => 'fieldB',
                        '$achildren.0.$bchild'              => 'bchild',
                        '$achildren.0.$bchild.$fieldC'      => 'fieldC',
                        '$achildren.0.$bchildren'           => 'bchildren',
                        '$achildren.0.$bchildren.0.$fieldC' => 'fieldC',

                    ],
                    $baseNamespace.'\B' => [
                        '$fieldB'                           => 'fieldB',
                        '$bchild'                           => 'bchild',
                        '$bchildren'                        => 'bchildren',

                        '$bchild.$fieldC'                   => 'fieldC',
                        '$bchildren.0.$fieldC'              => 'fieldC',
                    ],
                    $baseNamespace.'\C' => [
                        '$fieldC'                           => 'fieldC',
                    ],
                ]
            );

        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/form')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/form')
                ->name('*.xml')
        );

        $compilerPass = new DocumentFormDataMapCompilerPass($documentMap);
        $compilerPass->process($containerDouble);
    }
}
