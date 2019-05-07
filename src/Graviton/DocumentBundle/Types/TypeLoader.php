<?php
/**
 * doctrine type loader
 */

namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TypeLoader
{

    /**
     * registers our types in doctrine
     *
     * @return void
     */
    public static function load()
    {
        Type::registerType('extref', ExtReferenceType::class);
        Type::registerType('translatable', TranslatableType::class);
        Type::registerType('translatablearray', TranslatableArrayType::class);
        Type::registerType('hash', HashType::class);
        Type::registerType('hasharray', HashArrayType::class);
        Type::registerType('datearray', DateArrayType::class);
    }
}
