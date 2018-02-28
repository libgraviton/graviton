<?php
/**
 * trait for test functions
 */

namespace Graviton\TestBundle\Test;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
trait PrivateClassMethodTrait
{

    /**
     * Testing private methods for a class.
     *
     * $class = new YourClass(); or service...
     * $method = $this->getPrivateClassMethod(get_class($class), 'getPrivateFunction');
     * $result = $method->invokeArgs( $this->activityManager, [argument1, argument2, ...]);
     *
     * @param string $className  String name for class, full namespace.
     * @param string $methodName Method name to be used
     *
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    public function getPrivateClassMethod($className, $methodName)
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * provide a protected property of a given object.
     *
     * @param string $className    String name for class, full namespace.
     * @param string $propertyName property name
     *
     * @return \ReflectionProperty
     * @throws \ReflectionException
     */
    public function getPrivateClassProperty($className, $propertyName)
    {
        $reflector = new \ReflectionClass($className);
        $prop = $reflector->getProperty($propertyName);
        $prop->setAccessible(true);
        return $prop;
    }
}
