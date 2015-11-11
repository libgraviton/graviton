<?php
/**
 * ExtReferenceConverterInterface class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\DocumentBundle\Entity\ExtReference;

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
     * return the extref from URL
     *
     * @param string $url Extref URL
     * @return ExtReference
     * @throws \InvalidArgumentException
     */
    public function getExtReference($url);

    /**
     * return the URL from extref
     *
     * @param ExtReference $extReference Extref
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl(ExtReference $extReference);
}
