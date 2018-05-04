<?php

namespace Graviton\RestBundle\Service;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonPatchValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider invalidJsonPatchesProvider
     * @expectedException \Exception
     */
    public function testInvalidJsonPatches($targetDocument, $jsonPatch)
    {
        $validator = self::validator();
        $validator->validate($targetDocument, $jsonPatch);
    }

    /**
     * @dataProvider validJsonPatchesProvider
     */
    public function testValidJsonPatches($targetDocument, $jsonPatch)
    {
        $this->assertTrue(self::validator()->validate($targetDocument, $jsonPatch));
    }

    /**
     * @return array
     */
    public static function invalidJsonPatchesProvider()
    {
        return [
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":"/nestedApps/7","value":{"name":"seven"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":"/nestedApps/3","value":{"name":"seven"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":"nestedApps/1","value":{"name":"seven"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":0,"value":{"name":"pointer is not string"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":"someBadPath","value":"test"}]'
            ]
        ];
    }

    /**
     * @return array
     */
    public static function validJsonPatchesProvider()
    {
        return [
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"}]}',
                '[{"op":"add","path":"/nestedApps/0","value":{"name":"new"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"},{"name":"three"}]}',
                '[{"op":"add","path":"/nestedApps/2","value":{"name":"new element"}}]'
            ],
            [
                '{"nestedApps":[{"name":"one"},{"name":"two"},{"name":"three"}]}',
                '[{"op":"add","path":"/nestedApps/3","value":{"name":"element to the end"}}]'
            ],
            [
                '{"unstructuredObject":{"hashField":{"id":500,"name":"one"}}}',
                '[{"op":"add","path":"/unstructuredObject/hashField/newField","value":"some string"}]'
            ]
        ];
    }

    /**
     * @return JsonPatchValidator
     */
    private static function validator()
    {
        return new JsonPatchValidator();
    }
}
