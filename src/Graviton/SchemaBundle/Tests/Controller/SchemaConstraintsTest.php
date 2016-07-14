<?php
/**
 * integration tests for our supported constraints
 */

namespace Graviton\SchemaBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $this->assertEquals($field, $results[0]->propertyPath);
        $this->assertEquals($errorMessage, $results[0]->message);
    }

    /**
     * Data provider for constraint test
     *
     * @return array data
     */
    public function schemaConstraintDataProvider()
    {
        return [

            // Choice

            'choice-string' => [
                'field' => 'choiceString',
                'acceptedValue' => 'a lo mejor',
                'rejectedValue' => 'no puedo',
                'errorMessage' => 'Does not have a value in the enumeration ["si","no","a lo mejor","mas"]'
            ],
            'choice-integer' => [
                'field' => 'choiceInteger',
                'acceptedValue' => 0,
                'rejectedValue' => 5,
                'errorMessage' => 'Does not have a value in the enumeration [0,1,2]'
            ],

            // Email

            'email' => [
                'field' => 'email',
                'acceptedValue' => 'hans.hofer@swisscom.com',
                'rejectedValue' => 'invalidemail@sss.',
                'errorMessage' => 'Invalid email'
            ],

            // Url

            'url' => [
                'field' => 'url',
                'acceptedValue' => 'https://github.com/libgraviton/graviton',
                'rejectedValue' => 'jjj--no-url',
                'errorMessage' => 'Invalid URL format'
            ],

            // Range

            'range-integer-lower-bound' => [
                'field' => 'rangeInteger',
                'acceptedValue' => 5,
                'rejectedValue' => 4,
                'errorMessage' => 'Must have a minimum value of 5'
            ],
            'range-integer-upper-bound' => [
                'field' => 'rangeInteger',
                'acceptedValue' => 9,
                'rejectedValue' => 10,
                'errorMessage' => 'Must have a maximum value of 9'
            ],
            'range-double-lower-bound' => [
                'field' => 'rangeDouble',
                'acceptedValue' => 0.0,
                'rejectedValue' => -0.0001,
                'errorMessage' => 'Must have a minimum value of 0'
            ],
            'range-double-upper-bound' => [
                'field' => 'rangeDouble',
                'acceptedValue' => 1.0,
                'rejectedValue' => 1.0000001,
                'errorMessage' => 'Must have a maximum value of 1'
            ],
            'range-integer-only-min' => [
                'field' => 'rangeIntegerOnlyMin',
                'acceptedValue' => 5,
                'rejectedValue' => 4,
                'errorMessage' => 'Must have a minimum value of 5'
            ],
            'range-integer-only-max' => [
                'field' => 'rangeIntegerOnlyMax',
                'acceptedValue' => 5,
                'rejectedValue' => 6,
                'errorMessage' => 'Must have a maximum value of 5'
            ],

            // GreatherThanOrEqual

            'greaterthan-integer' => [
                'field' => 'greaterThanOrEqualInt',
                'acceptedValue' => 0,
                'rejectedValue' => -1,
                'errorMessage' => 'Must have a minimum value of 0'
            ],
            'greaterthan-double' => [
                'field' => 'greaterThanOrEqualDouble',
                'acceptedValue' => 0.1,
                'rejectedValue' => 0,
                'errorMessage' => 'Must have a minimum value of 0.1'
            ],

            // LessThanOrEqual

            'lessthan-integer' => [
                'field' => 'lessThanOrEqualInt',
                'acceptedValue' => 0,
                'rejectedValue' => 1,
                'errorMessage' => 'Must have a maximum value of 0'
            ],
            'lessthan-double' => [
                'field' => 'lessThanOrEqualDouble',
                'acceptedValue' => 0.1,
                'rejectedValue' => 0.1000001,
                'errorMessage' => 'Must have a maximum value of 0.1'
            ],

            // Decimal (a decimal formatted string field)

            'decimal-string' => [
                'field' => 'decimalField',
                'acceptedValue' => '1000000000.5555',
                'rejectedValue' => '1.55555', // too much precision
                'errorMessage' => 'Does not match the regex pattern ^[+\-]?\d+(\.\d{0,4})?$'
            ],
            'decimal-string-notation' => [
                'field' => 'decimalField',
                'acceptedValue' => '1000000000',
                'rejectedValue' => '1,0', // wrong separator
                'errorMessage' => 'Does not match the regex pattern ^[+\-]?\d+(\.\d{0,4})?$'
            ],
            'decimal-string-string' => [
                'field' => 'decimalField',
                'acceptedValue' => '0',
                'rejectedValue' => 'somestring', // string
                'errorMessage' => 'Does not match the regex pattern ^[+\-]?\d+(\.\d{0,4})?$'
            ],

            // Count (number of array elements)

            'count-array-lower' => [
                'field' => 'arrayCount',
                'acceptedValue' => ['a'],
                'rejectedValue' => [],
                'errorMessage' => 'There must be a minimum of 1 items in the array'
            ],
            'count-array-upper' => [
                'field' => 'arrayCount',
                'acceptedValue' => ['a', 'b', 'c'],
                'rejectedValue' => ['a', 'b', 'c', 'd'],
                'errorMessage' => 'There must be a maximum of 3 items in the array'
            ]


        ];
    }
}
