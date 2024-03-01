<?php
/**
 * integration tests for our supported constraints
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaConstraintsTest extends RestTestCase
{
    /**
     * tests schema based validation constraints
     *
     * @param string $field         field
     * @param string $acceptedValue accepted value
     * @param string $rejectedValue rejected value
     * @param string $errorMessage  expected error message
     *
     * @dataProvider schemaConstraintDataProvider
     *
     * @return void
     */
    public function testSchemaConstraint($field, $acceptedValue, $rejectedValue, $errorMessage)
    {
        // test accepted value
        $object = new \stdClass();
        $object->{$field} = $acceptedValue;

        $client = static::createRestClient();
        $client->post('/testcase/schema-constraints/', $object);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());

        // test rejected value
        $object = new \stdClass();
        $object->{$field} = $rejectedValue;

        $client = static::createRestClient();
        $client->post('/testcase/schema-constraints/', $object);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $results = $client->getResults();
        $this->assertEquals($field, $results[1]->propertyPath);
        $this->assertStringContainsString($errorMessage, $results[1]->message);
    }

    /**
     * Data provider for constraint test
     *
     * @return array data
     */
    public static function schemaConstraintDataProvider(): array
    {
        return [

            // Empty

            'empty-string' => [
                'field' => 'emptyField',
                'acceptedValue' => '',
                'rejectedValue' => 'h',
                'errorMessage' => 'Length of \'h\' must be shorter or equal to 0'
            ],

            // Choice

            'choice-string' => [
                'field' => 'choiceString',
                'acceptedValue' => 'a lo mejor',
                'rejectedValue' => 'no puedo',
                'errorMessage' => 'Value must be present in the enum'
            ],
            'choice-integer' => [
                'field' => 'choiceInteger',
                'acceptedValue' => 0,
                'rejectedValue' => 5,
                'errorMessage' => 'Value must be present in the enum'
            ],

            // Email

            'email' => [
                'field' => 'email',
                'acceptedValue' => 'hans.hofer@swisscom.com',
                'rejectedValue' => 'invalidemail@sss.',
                'errorMessage' => 'does not match format email'
            ],

            // Url

            'url' => [
                'field' => 'url',
                'acceptedValue' => 'https://github.com/libgraviton/graviton',
                'rejectedValue' => 'jjj--no-url',
                'errorMessage' => 'does not match format uri of type string'
            ],

            // Range

            'range-integer-lower-bound' => [
                'field' => 'rangeInteger',
                'acceptedValue' => 5,
                'rejectedValue' => 4,
                'errorMessage' => 'Value 4 must be greater or equal to 5'
            ],
            'range-integer-upper-bound' => [
                'field' => 'rangeInteger',
                'acceptedValue' => 9,
                'rejectedValue' => 10,
                'errorMessage' => 'Value 10 must be less or equal to 9'
            ],
            'range-double-lower-bound' => [
                'field' => 'rangeDouble',
                'acceptedValue' => 0.0,
                'rejectedValue' => -0.0001,
                'errorMessage' => 'Value 0 must be greater or equal to 0'
            ],
            'range-double-upper-bound' => [
                'field' => 'rangeDouble',
                'acceptedValue' => 1.0,
                'rejectedValue' => 1.0000001,
                'errorMessage' => 'Value 1 must be less or equal to 1'
            ],
            'range-integer-only-min' => [
                'field' => 'rangeIntegerOnlyMin',
                'acceptedValue' => 5,
                'rejectedValue' => 4,
                'errorMessage' => 'Value 4 must be greater or equal to 5'
            ],
            'range-integer-only-max' => [
                'field' => 'rangeIntegerOnlyMax',
                'acceptedValue' => 5,
                'rejectedValue' => 6,
                'errorMessage' => 'Value 6 must be less or equal to 5'
            ],

            // GreatherThanOrEqual

            'greaterthan-integer' => [
                'field' => 'greaterThanOrEqualInt',
                'acceptedValue' => 0,
                'rejectedValue' => -1,
                'errorMessage' => 'Value -1 must be greater or equal to 0'
            ],
            'greaterthan-double' => [
                'field' => 'greaterThanOrEqualDouble',
                'acceptedValue' => 0.1,
                'rejectedValue' => 0,
                'errorMessage' => 'Value 0 must be greater or equal to 0'
            ],

            // LessThanOrEqual

            'lessthan-integer' => [
                'field' => 'lessThanOrEqualInt',
                'acceptedValue' => 0,
                'rejectedValue' => 1,
                'errorMessage' => 'Value 1 must be less or equal to 0'
            ],
            'lessthan-double' => [
                'field' => 'lessThanOrEqualDouble',
                'acceptedValue' => 0.1,
                'rejectedValue' => 0.1000001,
                'errorMessage' => 'Value 0 must be less or equal to 0'
            ],

            // Decimal (a decimal formatted string field)

            'decimal-string' => [
                'field' => 'decimalField',
                'acceptedValue' => '1000000000.5555',
                'rejectedValue' => '1,0', // wrong separator
                'errorMessage' => 'Data does not match pattern'
            ],
            'decimal-string-notation' => [
                'field' => 'decimalField',
                'acceptedValue' => '1000000000',
                'rejectedValue' => '1.', // nothing after separator
                'errorMessage' => 'Data does not match pattern'
            ],
            'decimal-string-muchprecision' => [
                'field' => 'decimalField',
                'acceptedValue' => '1000000000.3333333333333333',
                'rejectedValue' => 'O', // other string
                'errorMessage' => 'Data does not match pattern'
            ],
            'decimal-string-minus' => [
                'field' => 'decimalField',
                'acceptedValue' => '-3.3333333333333',
                'rejectedValue' => ';1.0', // wrong prefix
                'errorMessage' => 'Data does not match pattern'
            ],
            'decimal-string-plus' => [
                'field' => 'decimalField',
                'acceptedValue' => '+3.3333333333333',
                'rejectedValue' => ';1.0', // wrong prefix
                'errorMessage' => 'Data does not match pattern'
            ],
            'decimal-string-string' => [
                'field' => 'decimalField',
                'acceptedValue' => '0',
                'rejectedValue' => 'somestring', // string
                'errorMessage' => 'Data does not match pattern'
            ],

            // Count (number of array elements)

            'count-array-lower' => [
                'field' => 'arrayCount',
                'acceptedValue' => ['a'],
                'rejectedValue' => [],
                'errorMessage' => 'Size of an array must be greater or equal to 1'
            ],
            'count-array-upper' => [
                'field' => 'arrayCount',
                'acceptedValue' => ['a', 'b', 'c'],
                'rejectedValue' => ['a', 'b', 'c', 'd'],
                'errorMessage' => 'Size of an array must be less or equal to 3'
            ]


        ];
    }
}
