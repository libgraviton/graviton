<?php
/**
 * Handle x-dynamic-key
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

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
     */
    public static function resolveRef($fields, $refMethods)
    {
        $records = [];
        $functions = self::prepareFunctionNames($refMethods);
        foreach ($fields as $record) {
            $ref = $record->$functions[0]();
            for ($i = 1; $i < count($functions); $i++) {
                $ref = $ref->$functions[$i]();
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
