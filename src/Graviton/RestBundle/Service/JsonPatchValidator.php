<?php
/**
 * Class for validation JSON Patch for target document using JSON Pointer
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\SchemaBundle\Constraint\VersionServiceConstraint;
use Graviton\SchemaBundle\Document\Schema;
use Rs\Json\Pointer;
use Rs\Json\Pointer\InvalidPointerException;
use Rs\Json\Pointer\NonexistentValueReferencedException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class JsonPatchValidator
{
    /**
     * @param string $targetDocument JSON of target document
     * @param string $jsonPatch      Patch string
     * @param Object $schema         stdClass schema For Validation
     * @return boolean
     * @throws InvalidJsonPatchException
     */
    public function validate($targetDocument, $jsonPatch, $schema = null)
    {
        $schema = ($schema instanceof \stdClass) ? $schema : new \stdClass();
        $versioning = property_exists($schema, 'x-versioning') ? (boolean) $schema->{'x-versioning'} : false;

        $operations = json_decode($jsonPatch, true);
        $pointer = new Pointer($targetDocument);

        $paths = [];

        foreach ($operations as $op) {
            try {
                $pointer->get($op['path']);
                $paths[] = $op['path'];
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

        if ($versioning &&!in_array('/'.VersionServiceConstraint::FIELD_NAME, $paths)) {
            $msg = sprintf(
                'Versioned documents require that the field \'%s\' is in each patch request. ',
                VersionServiceConstraint::FIELD_NAME
            );
            throw new InvalidJsonPatchException($msg);
        }

        return true;
    }
}
