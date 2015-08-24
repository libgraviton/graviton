<?php
/**
 * BaseJsonDefinitionFieldTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\Schema;

/**
 * Base JsonDefinitionField test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class BaseJsonDefinitionFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Schema\Field
     */
    protected function getBaseField()
    {
        return (new Schema\Field())
            ->setName('name')
            ->setExposeAs('exposeAs')
            ->setTitle('title')
            ->setDescription('description')
            ->setType('type')
            ->setLength(10)
            ->setReadOnly(true)
            ->setTranslatable(true)
            ->setRequired(true)
            ->setCollection(['CollectionName'])
            ->setConstraints(
                [
                    (new Schema\Constraint())
                        ->setName('constraint1')
                        ->setOptions(
                            [
                                (new Schema\ConstraintOption())
                                    ->setName('option1')
                                    ->setValue('value1'),
                                (new Schema\ConstraintOption())
                                    ->setName('option2')
                                    ->setValue('value2'),
                            ]
                        ),
                    (new Schema\Constraint())
                        ->setName('constraint2')
                        ->setOptions(
                            [
                                (new Schema\ConstraintOption())
                                    ->setName('option3')
                                    ->setValue('value3'),
                                (new Schema\ConstraintOption())
                                    ->setName('option4')
                                    ->setValue('value4'),
                            ]
                        ),
                ]
            );
    }

    /**
     * @param Schema\Field $field
     * @return array
     */
    protected function getBaseDefAsArray(Schema\Field $field)
    {
        return [
            'length'            => $field->getLength(),
            'title'             => $field->getTitle(),
            'description'       => $field->getDescription(),
            'readOnly'          => $field->getReadOnly(),
            'required'          => $field->getRequired(),
            'translatable'      => $field->getTranslatable(),
            'collection'        => $field->getCollection(),
            'constraints'       => array_map(
                function (Schema\Constraint $constraint) {
                    return [
                        'name'      => $constraint->getName(),
                        'options'   => array_map(
                            function (Schema\ConstraintOption $option) {
                                return [
                                    'name'  => $option->getName(),
                                    'value' => $option->getValue(),
                                ];
                            },
                            $constraint->getOptions()
                        )
                    ];
                },
                $field->getConstraints()
            ),
        ];
    }
}
