<?php
/**
 * test abstract strategy
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\SecurityBundle\Tests\Authentication\Strategies\AbstractHttpStrategyProxy;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * Class AbstractHttpStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AbstractHttpStrategyTest extends RestTestCase
{
    /**
     * check that arguments are required
     *
     * @return void
     */
    public function testExtractFieldInfoExpectingException()
    {
        $strategy = new AbstractHttpStrategyProxy();

        $this->expectException('\InvalidArgumentException');

        $strategy->extractFieldInfo('invalid argument', 'Tux');
    }

    /**
     * @dataProvider fieldInfoProvider
     *
     * @param string $fieldContent test content for field
     *
     * @return void
     */
    public function testValidateFieldExpectingException($fieldContent = '')
    {
        $fieldName = 'my special field';

        $headerMock = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->setMethods(array('has', 'get'))
            ->getMock();
        $headerMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($fieldName))
            ->willReturn(true);
        $headerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($fieldName))
            ->willReturn($fieldContent);

        $strategy = new AbstractHttpStrategyProxy();

        $this->expectException('\Symfony\Component\HttpKernel\Exception\HttpException');

        $strategy->validateField($headerMock, $fieldName);
    }

    /**
     * @return array<string>
     */
    public function fieldInfoProvider()
    {
        return array(
            'field not in header' => array(''),
            'field empty' => array("\n"),
            'field has invalid content (whitespaces)' => array("\n\t\r\n \s"),
        );
    }
}
