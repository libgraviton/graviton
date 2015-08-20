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
     * Convert request to form data
     *
     * @param string $request   Request data
     * @param string $className Document class
     * @return array
     */
    public function convertToFormData($request, $className);
}
