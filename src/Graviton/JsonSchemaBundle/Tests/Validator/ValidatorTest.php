<?php
/**
 * ValidatorTest class file
 */

namespace Graviton\JsonSchemaBundle\Tests\Validator;

use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;
use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;
use Graviton\JsonSchemaBundle\Validator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Test Validator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValidatorTest extends TestCase
{
    /**
     * Test Validator::validateJsonDefinition() with malformed JSON
     *
     * @return void
     */
    public function testValidateJsonDefinitionMalformedJson()
    {
        $schema = new \stdClass();
        $json = '[}';

        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Malformed JSON');

        $validator = $this->getMockBuilder('JsonSchema\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->never())
            ->method('isValid');
        $validator->expects($this->never())
            ->method('getErrors');
        $validator->expects($this->never())
            ->method('validate');

        $sut = new Validator($validator, $schema);
        $this->assertEquals([], $sut->validateJsonDefinition($json));
    }

    /**
     * Test Validator::validateJsonDefinition() with non-object JSON
     *
     * @return void
     */
    public function testValidateJsonDefinitionNonObject()
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('JSON value must be an object');

        $schema = new \stdClass();
        $json = '[]';

        $validator = $this->getMockBuilder('JsonSchema\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->never())
            ->method('isValid');
        $validator->expects($this->never())
            ->method('getErrors');
        $validator->expects($this->never())
            ->method('validate');

        $sut = new Validator($validator, $schema);
        $this->assertEquals([], $sut->validateJsonDefinition($json));
    }

    /**
     * Test Validator::validateJsonDefinition() with errors
     *
     * @return void
     */
    public function testValidateJsonDefinitionWithErrors()
    {
        $schema = new \stdClass();
        $json = '{"a":"b"}';
        $errors = [['message' => 'error']];
        $returnErrors = [new ValidationExceptionError($errors[0])];

        $validator = $this->getMockBuilder('JsonSchema\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $validator->expects($this->once())
            ->method('getErrors')
            ->willReturn($errors);
        $validator->expects($this->once())
                  ->method('reset')
                  ->willReturn(true);
        $validator->expects($this->once())
            ->method('validate')
            ->with(json_decode($json), $schema);

        $sut = new Validator($validator, $schema);
        $this->assertEquals($returnErrors, $sut->validateJsonDefinition($json));
    }


    /**
     * Test Validator::validateJsonDefinition() without errors
     *
     * @return void
     */
    public function testValidateJsonDefinitionWithoutErrors()
    {
        $schema = new \stdClass();
        $json = '{"a":"b"}';

        $validator = $this->getMockBuilder('JsonSchema\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->with(json_decode($json), $schema)
            ->willReturn(true);
        $validator->expects($this->once())
                  ->method('isValid')
                  ->willReturn(true);
        $validator->expects($this->never())
            ->method('getErrors');

        $sut = new Validator($validator, $schema);
        $this->assertEquals([], $sut->validateJsonDefinition($json));
    }
}
