<?php

namespace Graviton\SecurityBundle\Tests;

use Graviton\SecurityBundle\Authorizator;
use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthorizatorTest extends GravitonTestCase
{
    /** @var  \stdClass */
    protected $object;

    /** @var Authorizator */
    protected $authorizator;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->object = new \stdClass();
        $checkerDouble =
            $this->getSimpleTestDouble('\Symfony\Component\Security\Core\Authorization\AuthorizationChecker');
        $checkerDouble
            ->expects($this->once())
            ->method('isGranted')
            ->with(
                $this->equalTo('VIEW'),
                $this->equalTo($object)
            )
            ->willReturn(true);

        $this->authorizator = new Authorizator($checkerDouble);
    }

    /**
     * @return void
     */
    public function testCanView()
    {
        $this->assertTrue($this->authorizator->canView($object));
    }

    /**
     * @return void
     */
    public function testCanCreate()
    {
        $this->assertTrue($this->authorizator->canCreate($object));
    }

    /**
     * @return void
     */
    public function testCanUpdate()
    {
        $this->assertTrue($this->authorizator->canUpdate($object));
    }

    /**
     * @return void
     */
    public function testCanDelete()
    {
        $this->assertTrue($this->authorizator->canDelete($object));
    }
}
