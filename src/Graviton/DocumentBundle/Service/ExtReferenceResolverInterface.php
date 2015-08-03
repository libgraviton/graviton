<?php
/**
 * ExtReferenceResolverInterface class file
 */

namespace Graviton\DocumentBundle\Service;

/**
 * Extref URL resolver interface
 */
interface ExtReferenceResolverInterface
{
    /**
     * return the mongodb representation from a extref URL
     *
     * @param string $url Extref URL
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getDbValue($url);

    /**
     * return the extref URL
     *
     * @param array $value DB value
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl(array $value);
}
