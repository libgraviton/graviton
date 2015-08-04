<?php
/**
 * ExtReferenceConverterInterface class file
 */

namespace Graviton\DocumentBundle\Service;

/**
 * Extref URL converter interface
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
     * @param array $dbRef DB ref
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl(array $dbRef);
}
