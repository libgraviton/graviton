<?php
/**
 * ConstraintNormalizer class file
 */
namespace Graviton\GeneratorBundle\Definition\Utils;

use Graviton\GeneratorBundle\Definition\Schema\Constraint;
use Graviton\GeneratorBundle\Definition\Schema\ConstraintOption;

/**
 * Constraint normalizer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConstraintNormalizer
{
    /**
     * Convert constraint to array to selerialile it via json_encode
     *
     * @param Constraint $constraint Constraint
     * @return array
     */
    public static function normalize(Constraint $constraint)
    {
        return [
            'name'      => $constraint->getName(),
            'options'   => array_map(
                function (ConstraintOption $option) {
                    return [
                        'name'  => $option->getName(),
                        'value' => $option->getValue(),
                    ];
                },
                $constraint->getOptions()
            ),
        ];
    }
}
