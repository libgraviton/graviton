<?php
/**
 * ProxyController
 */

namespace Graviton\ProxyBundle\Controller;

use Graviton\ProxyBundle\Service\ApiDefinitionLoader;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Proxy\Proxy;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * general controller for all proxy staff
 *
 * @package  Graviton\ProxyBundle\Controller
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyController
{
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var ApiDefinitionLoader
     */
    private $apiLoader;

    /**
     * @var array
     */
    private $proxySourceConfiguration;

    /**
     * Constructor
     *
     * @param Proxy               $proxy                    proxy
     * @param EngineInterface     $templating               twig templating engine
     * @param ApiDefinitionLoader $loader                   definition loader
     * @param array               $proxySourceConfiguration Set of sources to be recognized by the controller.
     */
    public function __construct(
        Proxy $proxy,
        EngineInterface $templating,
        ApiDefinitionLoader $loader,
        array $proxySourceConfiguration
    ) {
        $this->proxy = $proxy;
        $this->templating = $templating;
        $this->apiLoader = $loader;
        $this->proxySourceConfiguration = $proxySourceConfiguration;
    }

    /**
     * action for routing all requests directly to the third party API
     *
     * @param Request $request request
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public function proxyAction(Request $request)
    {
        $scheme = $request->getScheme();
        $api = $this->decideApiAndEndpoint($request->getUri());
        $this->registerProxySources();

        $url = $this->apiLoader->getEndpoint($api['endpoint'], true);
        $url = $scheme."://".$url;

        $response = null;
        try {
            $newRequest = Request::create(
                $request->getUri(),
                $request->getMethod(),
                array (),
                array (),
                array (),
                array (),
                $request->getContent(false)
            );
            $newRequest->headers->add($request->headers->all());
            $response = $this->proxy->forward($newRequest)->to($url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (ServerException $serverException) {
            $response = $serverException->getResponse();
        }

        return $response;
    }

    /**
     * get schema info
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function schemaAction(Request $request)
    {
        $api = $this->decideApiAndEndpoint($request->getUri());
        $this->registerProxySources();
        $schema = $this->apiLoader->getEndpointSchema($api['endpoint']);

        $response = new Response(json_encode($schema), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $this->templating->renderResponse(
            'GravitonCoreBundle:Main:index.json.twig',
            array ('response' => $response->getContent()),
            $response
        );
    }

    /**
     * get API name and endpoint from the url (third party API)
     *
     * @param string $url the url
     *
     * @return array
     */
    protected function decideApiAndEndpoint($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        $pattern = array (
            "@schema\/@",
            "@\/3rdparty\/@",
            "@\/item$@",
        );
        $path = preg_replace($pattern, '', $path);

        //get api name and endpoint
        $apiName = substr($path, 0, strpos($path, '/'));
        $endpoint = str_replace($apiName, '', $path);

        return array (
            "apiName" => $apiName,
            "endpoint" => $endpoint,
        );
    }

    /**
     * Registers configured external services to be proxied.
     *
     * @return Void
     */
    private function registerProxySources()
    {
        if (array_key_exists('swagger', $this->proxySourceConfiguration)) {
            foreach ($this->proxySourceConfiguration['swagger'] as $config) {
                $this->apiLoader->setOption($config);
            }
        }
    }
}
