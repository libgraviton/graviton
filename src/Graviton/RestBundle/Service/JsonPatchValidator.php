<?php
/**
 * Class for validation JSON Patch for target document using JSON Pointer
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Rs\Json\Pointer;
use Rs\Json\Pointer\InvalidPointerException;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class JsonPatchValidator
{
    /**
     * @param string $targetDocument JSON of target document
     * @param string $jsonPatch
     * @return boolean
     * @throws InvalidJsonPatchException
     */
    public function validate($targetDocument, $jsonPatch)
    {
        $operations = json_decode($jsonPatch, true);
        $pointer = new Pointer($targetDocument);
        foreach ($operations as $op) {
            try {
                $pointer->get($op['path']);
            } catch (InvalidPointerException $e) {
                throw new InvalidJsonPatchException($e);
            } catch (NonexistentValueReferencedException $e) {
                $pathParts = explode('/', $op['path']);
                $lastPart = end($pathParts);

                if (is_numeric($lastPart)) {
                    /**
                     * JSON Pointer library throws an Exception when INDEX is equal to number of elements in array
                     * But JSON Patch allow this as described in RFC
                     *
                     * http://tools.ietf.org/html/rfc6902#section-4.1
                     * "The specified index MUST NOT be greater than the number of elements in the array."
                     */

                    // Try to check previous element
                    array_pop($pathParts);
                    array_push($pathParts, $lastPart - 1);

                    try {
                        $pointer->get(implode('/', $pathParts));
                    } catch (NonexistentValueReferencedException $e) {
                        throw new InvalidJsonPatchException($e);
                    }
                }
            }
        }

        return true;
    }
}
