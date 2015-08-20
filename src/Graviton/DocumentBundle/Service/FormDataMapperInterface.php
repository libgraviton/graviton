<?php
/**
 * FormDataMapperInterface class file
 */

namespace Graviton\DocumentBundle\Service;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface FormDataMapperInterface
{
    /**
     * Convert document to form data
     *
     * @param object $document  Document from request
     * @param string $className Document class
     * @return array
     */
    public function convertToFormData($document, $className);
}
