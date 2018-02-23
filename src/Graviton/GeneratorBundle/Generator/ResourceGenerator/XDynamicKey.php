<?php
/**
 * Handle x-dynamic-key
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Graviton\ExceptionBundle\Exception\XDynamicKeyException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
            $orgRec = $record;
            foreach ($functions as $function) {
                if (method_exists($record, $function)) {
                    $record = $record->$function();
                } else {
                    throw new XDynamicKeyException(
                        'x-dynamic-key ref-field could not be resolved: '.$function
                    );
                }
            }

            if ($record !== null) {
                $records[$record->getId()] = $orgRec;
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
