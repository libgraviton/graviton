<?php
/**
 * check if form builder field-map is being generated correctly
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormFieldsCompilerPassTest extends \PHPUnit_Framework_TestCase
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
                'graviton.document.form.type.document.field_map',
                [
                    'stdclass' => [],
                    $baseNamespace.'\A' => [
                        [
                            'id',
                            'id',
                            'text',
                            [],
                        ],
                        [
                            'integer',
                            'integerA',
                            'integer',
                            [],
                        ],
                        [
                            'title',
                            'titleA',
                            'translatable',
                            [],
                        ],
                        [
                            'extref',
                            'extrefA',
                            'extref',
                            [],
                        ],
                        [
                            'boolean',
                            'booleanA',
                            'checkbox',
                            [],
                        ],
                        [
                            'datetime',
                            'datetimeA',
                            'datetime',
                            [],
                        ],
                        [
                            'float',
                            'floatA',
                            'number',
                            [],
                        ],
                        [
                            'unstruct',
                            'unstructA',
                            'freeform',
                            [],
                        ],
                        [
                            'achild',
                            'achild',
                            'form',
                            [
                                'data_class' => $baseNamespace.'\B',
                                'required' => false,
                            ],
                        ],
                        [
                            'achildren',
                            'achildren',
                            'collection',
                            [
                                'type' => 'form',
                                'options' => ['data_class' => $baseNamespace.'\B'],
                            ],
                        ],
                    ],
                    $baseNamespace.'\B' => [
                        [
                            'id',
                            'id',
                            'text',
                            [],
                        ],
                        [
                            'field',
                            'fieldB',
                            'text',
                            [],
                        ],
                        [
                            'bchild',
                            'bchild',
                            'form',
                            [
                                'data_class' => $baseNamespace.'\C',
                                'required' => false,
                            ],
                        ],
                        [
                            'bchildren',
                            'bchildren',
                            'collection',
                            [
                                'type' => 'form',
                                'options' => ['data_class' => $baseNamespace.'\C'],
                            ],
                        ],
                    ],
                    $baseNamespace.'\C' => [
                        [
                            'id',
                            'id',
                            'text',
                            [],
                        ],
                        [
                            'field',
                            'fieldC',
                            'text',
                            [],
                        ],
                    ],
                ]
            );

        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/form')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/form')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/validation/form')
                ->name('*.xml')
        );

        $compilerPass = new DocumentFormFieldsCompilerPass($documentMap);
        $compilerPass->process($containerDouble);
    }
}
