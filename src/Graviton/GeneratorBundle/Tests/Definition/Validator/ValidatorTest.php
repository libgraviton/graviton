<?php
/**
 * ValidatorTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Validator;

use Graviton\GeneratorBundle\Definition\Validator\Validator;

/**
 * Test validator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Validator::validateJsonDefinition() with malformed JSON
     *
     * @return void
     * @expectedException \Graviton\GeneratorBundle\Definition\Validator\InvalidJsonException
     * @expectedExceptionMessage Malformed JSON
     */
    public function testValidateJsonDefinitionMalformedJson()
    {
        $schema = new \stdClass();
        $json = '[}';

        $validator = $this->getMockBuilder('HadesArchitect\JsonSchemaBundle\Validator\ValidatorServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->never())
            ->method('isValid');
        $validator->expects($this->never())
            ->method('getErrors');
        $validator->expects($this->never())
            ->method('check');

        $sut = new Validator($validator, $schema);
        $this->assertEquals([], $sut->validateJsonDefinition($json));
    }

    /**
     * Test Validator::validateJsonDefinition() with non-object JSON
     *
     * @return void
     * @expectedException \Graviton\GeneratorBundle\Definition\Validator\InvalidJsonException
     * @expectedExceptionMessage JSON value must be an object
     */
    public function testValidateJsonDefinitionNonObject()
    {
        $schema = new \stdClass();
        $json = '[]';

        $validator = $this->getMockBuilder('HadesArchitect\JsonSchemaBundle\Validator\ValidatorServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->never())
            ->method('isValid');
        $validator->expects($this->never())
            ->method('getErrors');
        $validator->expects($this->never())
            ->method('check');

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
        $errors = [__METHOD__];

        $validator = $this->getMockBuilder('HadesArchitect\JsonSchemaBundle\Validator\ValidatorServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('isValid')
            ->with(json_decode($json), $schema)
            ->willReturn(false);
        $validator->expects($this->once())
            ->method('getErrors')
            ->willReturn($errors);
        $validator->expects($this->never())
            ->method('check');

        $sut = new Validator($validator, $schema);
        $this->assertEquals($errors, $sut->validateJsonDefinition($json));
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

        $validator = $this->getMockBuilder('HadesArchitect\JsonSchemaBundle\Validator\ValidatorServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->once())
            ->method('isValid')
            ->with(json_decode($json), $schema)
            ->willReturn(true);
        $validator->expects($this->never())
            ->method('getErrors');
        $validator->expects($this->never())
            ->method('check');

        $sut = new Validator($validator, $schema);
        $this->assertEquals([], $sut->validateJsonDefinition($json));
    }
}
