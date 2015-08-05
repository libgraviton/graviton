<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Symfony\Component\Routing\RouterInterface;
use Graviton\DocumentBundle\Service\ExtReferenceJsonConverter;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceJsonConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl', 'getDbRef'])
            ->getMock();
    }

    /**
     * Test convert
     *
     * @return void
     */
    public function testConvert()
    {
        $before = [
            'name' => 'name',
            'ref' => '{"$ref":"toplevel","$id":123}',
            'array' => [
                '{"$ref":"array","$id":123}',
                '{"$ref":"array","$id":456}',
            ],
            'arrayhash' => [
                [
                    'ref' => '{"$ref":"arrayhash","$id":123}',
                ],
                [
                    'ref' => '{"$ref":"arrayhash","$id":456}',
                ],
            ],
            'hash' => [
                'c' => [
                    'ref' => '{"$ref":"hash","$id":123}',
                ],
                'd' => [
                    'ref' => '{"$ref":"notmapped","$id":123}',
                ],
            ],
            'deep' => [
                'deep' => [
                    'deep' => [
                        'deep' => [
                            'ref' => '{"$ref":"deep","$id":123}',
                        ],
                    ],
                ],
            ],
        ];
        $after = [
            'name' => 'name',
            'ref' => 'url-toplevel-123',
            'array' => [
                'url-array-123',
                'url-array-456',
            ],
            'hash' => [
                'c' => [
                    'ref' => 'url-hash-123',
                ],
                'd' => [
                    'ref' => '{"$ref":"notmapped","$id":123}',
                ],
            ],
            'arrayhash' => [
                [
                    'ref' => 'url-arrayhash-123',
                ],
                [
                    'ref' => 'url-arrayhash-456',
                ],
            ],
            'deep' => [
                'deep' => [
                    'deep' => [
                        'deep' => [
                            'ref' => 'url-deep-123',
                        ],
                    ],
                ],
            ],
        ];
        $fields = [
            'ref',
            'array.0',
            'hash.c.ref',
            'arrayhash.0.ref',
            'deep.deep.deep.deep.ref'
        ];

        $this->converter
            ->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(
                function ($url) {
                    $map = [
                        '{"$ref":"toplevel","$id":123}'     => 'url-toplevel-123',
                        '{"$ref":"array","$id":123}'        => 'url-array-123',
                        '{"$ref":"array","$id":456}'        => 'url-array-456',
                        '{"$ref":"hash","$id":123}'         => 'url-hash-123',
                        '{"$ref":"arrayhash","$id":123}'    => 'url-arrayhash-123',
                        '{"$ref":"arrayhash","$id":456}'    => 'url-arrayhash-456',
                        '{"$ref":"deep","$id":123}'         => 'url-deep-123',
                    ];

                    return $map[json_encode($url)];
                }
            );

        $converter = new ExtReferenceJsonConverter($this->converter);
        $this->assertEquals($after, $converter->convert($before, $fields));
    }
}
