<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Loader;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Loader\Loader;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;
use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * check if strategies are called
     *
     * @return void
     */
    public function testLoadCallsStrategy()
    {
        $json = __METHOD__;
        $definition = new Definition();

        $validator = $this->getMockBuilder('Graviton\JsonSchemaBundle\Validator\ValidatorInterface')
            ->disableOriginalConstructor()
            ->onlyMethods(['validateJsonDefinition'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validateJsonDefinition')
            ->with($json);

        $serializer = $this->getMockBuilder('Jms\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with($json, 'Graviton\GeneratorBundle\Definition\Schema\Definition', 'json')
            ->willReturn($definition);

        $strategy = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface')
            ->getMock();
        $strategy->expects($this->once())
            ->method('supports')
            ->with(null)
            ->will($this->returnValue(true));
        $strategy->expects($this->once())
            ->method('load')
            ->with(null)
            ->will($this->returnValue([$json]));

        $sut = new Loader($validator, $serializer);
        $sut->addStrategy($strategy);
        $this->assertEquals([new JsonDefinition($definition)], $sut->load(null));
    }


    /**
     * check if JSON is invalid
     *
     * @return void
     */
    public function testLoadInvalidJson()
    {
        $this->expectException(InvalidJsonException::class);
        $json = __METHOD__;

        $validator = $this->getMockBuilder('Graviton\JsonSchemaBundle\Validator\ValidatorInterface')
            ->disableOriginalConstructor()
            ->onlyMethods(['validateJsonDefinition'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validateJsonDefinition')
            ->with($json)
            ->willThrowException(new InvalidJsonException());

        $serializer = $this->getMockBuilder('Jms\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->onlyMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->never())
            ->method('deserialize');

        $strategy = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface')
            ->getMock();
        $strategy->expects($this->once())
            ->method('supports')
            ->with(null)
            ->willReturn(true);
        $strategy->expects($this->once())
            ->method('load')
            ->with(null)
            ->willReturn([$json]);

        $sut = new Loader($validator, $serializer);
        $sut->addStrategy($strategy);
        $sut->load(null);
    }

    /**
     * check if schema is invalid
     *
     * @return void
     */
    public function testLoadInvalidDefinition()
    {
        $this->expectException(ValidationException::class);
        $json = __METHOD__;

        $errors = [new ValidationExceptionError(['message' => 'wrong', "property" => '.'])];

        $validator = $this->getMockBuilder('Graviton\JsonSchemaBundle\Validator\ValidatorInterface')
            ->disableOriginalConstructor()
            ->returnValue(['validateJsonDefinition'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validateJsonDefinition')
            ->with($json)
            ->willReturn($errors);

        $serializer = $this->getMockBuilder('Jms\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->returnValue(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->never())
            ->method('deserialize');

        $strategy = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface')
            ->getMock();
        $strategy->expects($this->once())
            ->method('supports')
            ->with(null)
            ->willReturn(true);
        $strategy->expects($this->once())
            ->method('load')
            ->with(null)
            ->willReturn([$json]);

        $sut = new Loader($validator, $serializer);
        $sut->addStrategy($strategy);
        $sut->load(null);
    }
}
