<?php
/**
 * functions to initialize values
 */

namespace Graviton\CoreBundle\ValueInitializer;

use Graviton\CoreBundle\ValueInitializer\Initializer\CurrentDateInitializer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValueInitializer
{

    private static bool $isInitialized = false;

    private static array $initializers = [];

    /**
     * initialize
     *
     * @return void
     */
    private static function initialize(): void
    {
        self::$initializers['currentDate'] = new CurrentDateInitializer();
    }

    /**
     * does an initializer exist?
     *
     * @param string $initializerName name
     *
     * @return bool yes or no
     */
    public static function doesExist(string $initializerName): bool
    {
        if (!self::$isInitialized) {
            self::initialize();
        }

        return isset(self::$initializers[$initializerName]);
    }

    /**
     * gets the value from an initializer
     *
     * @param string $initializer  name
     * @param mixed  $presentValue current value
     *
     * @return mixed new value
     */
    public static function getInitialValue(string $initializer, mixed $presentValue) : mixed
    {
        if (!self::doesExist($initializer)) {
            throw new \RuntimeException('ValueInitializer "'.$initializer.'" does not exist!');
        }

        return self::$initializers[$initializer]->getInitialValue($presentValue);
    }
}
