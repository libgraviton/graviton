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
                            'text',
                            ['property_path' => 'id'],
                        ],
                        [
                            'integerA',
                            'integer',
                            ['property_path' => 'integer'],
                        ],
                        [
                            'titleA',
                            'translatable',
                            ['property_path' => 'title', 'required' => false],
                        ],
                        [
                            'extrefA',
                            'extref',
                            ['property_path' => 'extref'],
                        ],
                        [
                            'booleanA',
                            'strictboolean',
                            ['property_path' => 'boolean'],
                        ],
                        [
                            'datetimeA',
                            'datetime',
                            ['property_path' => 'datetime'],
                        ],
                        [
                            'floatA',
                            'number',
                            ['property_path' => 'float'],
                        ],
                        [
                            'unstructA',
                            'freeform',
                            ['property_path' => 'unstruct'],
                        ],
                        [
                            'achild',
                            'form',
                            [
                                'property_path' => 'achild',
                                'data_class' => $baseNamespace.'\B',
                                'required' => false,
                            ],
                        ],
                        [
                            'achildren',
                            'collection',
                            [
                                'property_path' => 'achildren',
                                'type' => 'form',
                                'options' => ['data_class' => $baseNamespace.'\B'],
                            ],
                        ],
                    ],
                    $baseNamespace.'\B' => [
                        [
                            'id',
                            'text',
                            ['property_path' => 'id'],
                        ],
                        [
                            'fieldB',
                            'text',
                            ['property_path' => 'field'],
                        ],
                        [
                            'bchild',
                            'form',
                            [
                                'property_path' => 'bchild',
                                'data_class' => $baseNamespace.'\C',
                                'required' => true,
                            ],
                        ],
                        [
                            'bchildren',
                            'collection',
                            [
                                'property_path' => 'bchildren',
                                'type' => 'form',
                                'options' => ['data_class' => $baseNamespace.'\C'],
                            ],
                        ],
                    ],
                    $baseNamespace.'\C' => [
                        [
                            'id',
                            'text',
                            ['property_path' => 'id'],
                        ],
                        [
                            'fieldC',
                            'text',
                            ['property_path' => 'field'],
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
