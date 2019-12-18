<?php
/**
 * doctrine type loader
 */

namespace Graviton\DocumentBundle\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Entity\Translatable;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TypeLoader
{

    /**
     * Backwards mapping from serializer type to our one for the generator!
     * Don't miss to put your own types here
     *
     * @var array
     */
    public static $classTypeMapping = [
        ExtReference::class => 'extref',
        Translatable::class => 'translatable',
        'array<' . Translatable::class . '>' => 'translatablearray',
        Hash::class => 'hash',
        'array<' . Hash::class . '>' => 'hasharray'
    ];

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
