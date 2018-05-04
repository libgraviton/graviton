<?php
/**
 * test creation of models via model-factory
 */

namespace Graviton\SecurityBundle\User\Model;

/**
 * Class ModelFactoryTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ModelFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider modelServiceIdProvider
     * @covers \Graviton\SecurityBundle\User\Model\ModelFactory::__construct
     * @covers \Graviton\SecurityBundle\User\Model\ModelFactory::create
     *
     * @param string   $serviceId       service id
     * @param string[] $expectedService resulting class
     *
     * @return void
     */
    public function testCreate($serviceId, $expectedService)
    {
        $containerMock = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter', 'has', 'get'))
            ->getMockForAbstractClass();
        $containerMock
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('graviton.security.authentication.provider.model'))
            ->will($this->returnValue($serviceId));
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(new \Graviton\SecurityBundle\User\Model\NullModel()),
                    $this->returnValue(new \Graviton\SecurityBundle\User\Model\NullModel())
                )
            );
        $containerMock
            ->expects($this->any())
            ->method('has')
            ->with($this->equalTo($serviceId))
            ->will($this->returnValue(true));

        $factory = new ModelFactory($containerMock);

        $service = $factory->create();

        $this->assertInstanceOf('\Graviton\RestBundle\Model\ModelInterface', $service);
        $this->assertEquals($expectedService, get_class($service));
    }

    /**
     * provide service ids for testCreate()
     *
     * @return string<string>
     */
    public function modelServiceIdProvider()
    {
        return array(
            'no service id provided' => array(null, 'Graviton\SecurityBundle\User\Model\NullModel'),
            'some service id provided' => array(
                'graviton.security.authentication.provider.model.noop',
                'Graviton\SecurityBundle\User\Model\NullModel'
            ),
        );
    }
}
