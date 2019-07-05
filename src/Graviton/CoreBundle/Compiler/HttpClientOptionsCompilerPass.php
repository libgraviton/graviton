<?php
/** compiles all necessary guzzle client options in their final form */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HttpClientOptionsCompilerPass implements CompilerPassInterface
{

    /**
     * proxy settings
     *
     * @var array
     */
    private $proxySettings = [];

    /**
     * add guzzle options
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $baseOptions = [
            'verify' => $container->getParameter('graviton.core.httpclient.verifyPeer')
        ];

        // system settings envs
        if (isset($_ENV['HTTP_PROXY']) && !empty($_ENV['HTTP_PROXY'])) {
            $this->setProxy($_ENV['HTTP_PROXY'], 'http');
        }
        if (isset($_ENV['HTTPS_PROXY']) && !empty($_ENV['HTTPS_PROXY'])) {
            $this->setProxy($_ENV['HTTPS_PROXY'], 'https');
        }
        if (isset($_ENV['NO_PROXY']) && !empty($_ENV['NO_PROXY'])) {
            $this->setNoProxyList($_ENV['NO_PROXY']);
        }

        // is there the old setting?
        if (isset($_ENV['GRAVITON_PROXY_CURLOPTS'])) {
            $yamlSettings = Yaml::parse($_ENV['GRAVITON_PROXY_CURLOPTS']);
            if (is_array($yamlSettings) && isset($yamlSettings['proxy'])) {
                $this->setProxy($yamlSettings['proxy']);
            }
            if (is_array($yamlSettings) && isset($yamlSettings['noproxy'])) {
                $this->setNoProxyList($yamlSettings['noproxy']);
            }
        }

        // new settings -> override all
        if (null !== $container->getParameter('graviton.proxy')) {
            $this->setProxy($container->getParameter('graviton.proxy'));
        }
        if (null !== $container->getParameter('graviton.noproxy')) {
            $this->setNoProxyList($container->getParameter('graviton.noproxy'));
        }

        // any proxy?
        if (!empty($this->proxySettings)) {
            $baseOptions['proxy'] = $this->proxySettings;
        }

        $container->setParameter(
            'graviton.core.http.client.options',
            $baseOptions
        );
    }

    /**
     * set the proxy
     *
     * @param string       $proxy        the proxy
     * @param null|boolean $onlyProtocol only set certain protocol
     *
     * @return void
     */
    private function setProxy($proxy, $onlyProtocol = null)
    {
        $proxy = trim($proxy);
        if (null == $onlyProtocol) {
            $this->proxySettings['http'] = $proxy;
            $this->proxySettings['https'] = $proxy;
        } else {
            $this->proxySettings[$onlyProtocol] = $proxy;
        }
    }

    /**
     * set the no proxy setting
     *
     * @param string $list either string or array
     *
     * @return void
     */
    private function setNoProxyList($list)
    {
        if (is_array($list)) {
            $this->proxySettings['no'] = array_map('trim', $list);
            return;
        }

        if (is_string($list) && strpos($list, ',') !== false) {
            $this->proxySettings['no'] = explode(',', $list);
        } elseif (is_string($list) && strpos($list, ' ') !== false) {
            $this->proxySettings['no'] = explode(' ', $list);
        }
        $this->proxySettings['no'] = array_map('trim', $this->proxySettings['no']);
    }
}
