<?php
/**
 * test security contract (mainly getters and defaults)
 */

namespace Graviton\SecurityBundle\Entities;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Class SecurityContractTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SecurityAnonymousTest extends RestTestCase
{
    /**
     * @param string[] $methods methods to mock
     *
     * @return \Graviton\SecurityBundle\Entities\AnonymousUser
     */
    protected function getUserMock(array $methods = array())
    {
        return $this->getMockBuilder('\Graviton\SecurityBundle\Entities\AnonymousUser')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * test getting username from contract
     *
     * @return void
     */
    public function testUsername()
    {
        $customerMock = $this->getMockBuilder('\Graviton\SecurityBundle\Entities\SecurityUser')
            ->disableOriginalConstructor()
            ->setMethods(array('getUsername', 'getId'))
            ->getMock();
        $customerMock
            ->expects($this->never())
            ->method('getId')
            ->will($this->returnValue(0));
        $customerMock
            ->expects($this->never())
            ->method('getUsername')
            ->will($this->returnValue('anonymous'));
    }
}
