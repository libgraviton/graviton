<?php
/**
 * DatetimeSchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DatetimeSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * @var string
     */
    private string $dateTimeRegex;

    /**
     * constructor.
     *
     * @param string $dateTimeRegex regex
     */
    public function __construct(string $dateTimeRegex)
    {
        $this->dateTimeRegex = $dateTimeRegex;
    }

    /**
     * gives the schemabuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition) : array
    {
        if ($fieldDefinition['schemaType'] == 'datetime[]') {
            $schemaField['type'] = 'array';
            $schemaField['items'] = [
                'type' => 'string',
                'format' => 'date-time',
                'pattern' => $this->dateTimeRegex
            ];
        } elseif ($fieldDefinition['schemaType'] == 'datetime') {
            $schemaField['type'] = 'string';
            $schemaField['format'] = 'date-time';
            $schemaField['pattern'] = $this->dateTimeRegex;
        }

        return $schemaField;
    }
}
