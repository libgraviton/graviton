<?php
/**
 * ExtReferenceConverterInterface class file
 */

namespace Graviton\DocumentBundle\Service;

/**
 * Extref converter interface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface ExtReferenceConverterInterface
{
    /**
     * return the mongodb representation from a extref URL
     *
     * @param string $url Extref URL
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getDbRef($url);

    /**
     * return the extref URL
     *
     * @param object $dbRef DB ref
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl($dbRef);
}
