<?php
/**
 * FormDataMapperTest class file
 */

namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\DocumentBundle\Service\FormDataMapper;

/**
 * FormDataMapper test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class FormDataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test FormDataMapper::convertToFormData()
     *
     * @return void
     */
    public function testConvertToFormData()
    {
        $fieldMap = [
            '$field'                => '_field_',

            '$hash.$field'          => '_hash_field_',
            '$hash'                 => '_hash_',

            '$arrayhash.0.$field'   => '_arrayhash_field_',
            '$arrayhash'            => '_arrayhash_',
        ];
        $requestData = (object) [
            '$field'        => 1,
            'abc'           => 11,
            '$hash'         => (object) [
                '$field'    => 2,
                'def'       => 22,
            ],
            '$arrayhash'    => [
                (object) [
                    '$field' => 3,
                    'ghi'    => 33,
                ],
                (object) [
                    '$field' => 4,
                    'ghi'    => 44,
                ],
            ],
        ];
        $formData = [
            '_field_'       => 1,
            'abc'           => 11,
            '_hash_'        => [
                '_hash_field_' => 2,
                'def'          => 22,
            ],
            '_arrayhash_'   => [
                [
                    '_arrayhash_field_' => 3,
                    'ghi'               => 33,
                ],
                [
                    '_arrayhash_field_' => 4,
                    'ghi'               => 44,
                ],
            ],
        ];

        $formDataMapper = new FormDataMapper(['A' => $fieldMap]);
        $this->assertEquals(
            $formData,
            $formDataMapper->convertToFormData(json_encode($requestData), 'A')
        );
    }
}
