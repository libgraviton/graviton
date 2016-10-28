<?php
/**
 * Provide a Proxy for third party APIs.
 */

namespace Graviton\ProxyExtensionBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\ProxyFundinfoExtensionBundle\GravitonProxyFundinfoExtensionBundle;
use Graviton\ProxyVontobelExtensionBundle\GravitonProxyVontobelExtensionBundle;
use Graviton\ProxyZugerkbExtensionBundle\GravitonProxyZugerkbExtensionBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonProxyExtensionBundle
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class GravitonProxyExtensionBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            new GravitonProxyFundinfoExtensionBundle(),
            new GravitonProxyVontobelExtensionBundle(),
            new GravitonProxyZugerkbExtensionBundle()
        );
    }
}
