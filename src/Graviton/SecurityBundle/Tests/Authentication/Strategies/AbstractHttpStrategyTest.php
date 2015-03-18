<?php
/**
 * test abstract strategy
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Graviton\SecurityBundle\Tests\Authentication\Strategies\AbstractHttpStrategyProxy;
use Graviton\TestBundle\Test\WebTestCase;

/**
 * Class AbstractHttpStrategyTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AbstractHttpStrategyTest extends WebTestCase
{
    /**
     * check that arguments are required
     *
     * @return void
     */
    public function testExtractFieldInfoExpectingException()
    {
        $strategy = new AbstractHttpStrategyProxy();

        $this->setExpectedException('\InvalidArgumentException');

        $strategy->extractFieldInfo('invalid argument', 'Tux');
    }

    /**
     * @dataProvider fieldInfoProvider
     *
     * @param boolean $hasField     is field in data
     * @param string  $fieldContent test content for field
     *
     * @return void
     */
    public function testValidateFieldExpectingException($hasField, $fieldContent = '')
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
            ->will($this->returnValue($hasField));
        $headerMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($fieldName))
            ->will($this->returnValue($fieldContent));

        $strategy = new AbstractHttpStrategyProxy();

        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\HttpException');

        $strategy->validateField($headerMock, $fieldName);
    }

    /**
     * @return array<string>
     */
    public function fieldInfoProvider()
    {
        return array(
            'field not in header' => array(false),
            'field empty' => array(true, "\n"),
            'field has invalid content (whitespaces)' => array(true, "\n\t\r\n \s"),
        );
    }
}
