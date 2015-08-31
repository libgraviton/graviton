<?php
/**
 * Class for validation JSON Patch for target document using JSON Pointer
 */

namespace Graviton\RestBundle\Service;

use Rs\Json\Pointer;
use Rs\Json\Pointer\InvalidPointerException;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class JsonPatchValidator
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @param string $targetDocument JSON of target document
     * @param string $jsonPatch
     * @return boolean
     */
    public function validate($targetDocument, $jsonPatch)
    {
        $operations = json_decode($jsonPatch, true);
        $pointer = new Pointer($targetDocument);
        foreach($operations as $op)
        {
            try {
                $pointer->get($op['path']);
            } catch(InvalidPointerException $e) {
                // Basic validation failed
                $this->setException($e);
                return false;
            } catch(NonexistentValueReferencedException $e) {
                $pathParts = explode('/', $op['path']);
                $lastPart = end($pathParts);

                if (is_numeric($lastPart)) {
                    $this->setException($e);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param \Exception $e
     */
    private function setException($e)
    {
        $this->exception = $e;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

}