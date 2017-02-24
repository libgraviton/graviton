<?php
/**
 * SourceInterface
 */

namespace Graviton\ProxyBundle\Service\Source;

/**
 * Interface SourceInterface
 *
 * @package Graviton\ProxyBundle\Service\Source
 */
interface SourceInterface
{
    /**
     * @return string
     */
    public function buildUrl();
}
