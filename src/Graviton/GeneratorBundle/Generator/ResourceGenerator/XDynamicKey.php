<?php
/**
 * Handle x-dynamic-key
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Graviton\ExceptionBundle\Exception\XDynamicKeyException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class XDynamicKey
{
    /**
     * @param  array  $fields     array of fields
     * @param  string $refMethods string containing "path" to the ref field
     * @return array
     *
     * @throws MethodNotFoundException
     */
    public static function resolveRef($fields, $refMethods)
    {
        $records = [];
        $functions = self::prepareFunctionNames($refMethods);
        foreach ($fields as $record) {
            if (method_exists($record, $functions[0])) {
                $ref = $record->$functions[0]();
                for ($i = 1, $imax = count($functions); $i < $imax; $i++) {
                    if (method_exists($ref, $functions[$i])) {
                        $ref = $ref->$functions[$i]();
                    } else {
                        throw new XDynamicKeyException();
                    }
                }
            } else {
                throw new XDynamicKeyException();
            }

            if ($ref !== null) {
                $records[$ref->getId()] = $record;
            }
        }

        return $records;
    }

    /**
     * prepares getter methods for every given field name
     *
     * @param  string $refMethods string containing "path" to the ref field
     * @return array
     */
    private static function prepareFunctionNames($refMethods)
    {
        $fieldNames = explode('.', $refMethods);

        $getters = [];
        foreach ($fieldNames as $field) {
            array_push($getters, 'get'.ucfirst($field));
        }

        return $getters;
    }
}
