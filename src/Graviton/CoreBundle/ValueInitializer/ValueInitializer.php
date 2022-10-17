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

    private static function initialize(): void
    {
        self::$initializers['currentDate'] = new CurrentDateInitializer();
    }

    public static function doesExist(string $initializerName): bool
    {
        if (!self::$isInitialized) {
            self::initialize();
        }

        return isset(self::$initializers[$initializerName]);
    }

    public static function getInitialValue(string $initializer, mixed $presentValue) : mixed
    {
        if (!self::doesExist($initializer)) {
            throw new \RuntimeException('ValueInitializer "'.$initializer.'" does not exist!');
        }

        return self::$initializers[$initializer]->getInitialValue($presentValue);
    }
}
