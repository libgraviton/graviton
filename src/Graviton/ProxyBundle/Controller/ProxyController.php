<?php
/**
 * ProxyController
 */

namespace Graviton\ProxyBundle\Controller;

use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ProxyBundle\Service\ApiDefinitionLoader;
use Graviton\ProxyBundle\Service\TransformationHandler;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Proxy\Proxy;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * general controller for all proxy staff
 *
 * @package Graviton\ProxyBundle\Controller
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
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
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var ApiDefinitionLoader
     */
    private $apiLoader;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var array
     */
    private $proxySourceConfiguration;

    /**
     * @var TransformationHandler
     */
    private $transformationHandler;

    /**
     * Constructor
     *
     * @param Proxy                 $proxy                    proxy
     * @param EngineInterface       $templating               twig templating engine
     * @param ApiDefinitionLoader   $loader                   definition loader
     * @param DiactorosFactory      $diactorosFactory         convert HttpFoundation objects to PSR-7
     * @param HttpFoundationFactory $httpFoundationFactory    convert PSR-7 interfaces to HttpFoundation
     * @param TransformationHandler $transformationHandler    transformation handler
     * @param array                 $proxySourceConfiguration Set of sources to be recognized by the controller.
     */
    public function __construct(
        Proxy $proxy,
        EngineInterface $templating,
        ApiDefinitionLoader $loader,
        DiactorosFactory $diactorosFactory,
        HttpFoundationFactory $httpFoundationFactory,
        TransformationHandler $transformationHandler,
        array $proxySourceConfiguration
    ) {
        $this->proxy = $proxy;
        $this->templating = $templating;
        $this->apiLoader = $loader;
        $this->diactorosFactory = $diactorosFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->proxySourceConfiguration = $proxySourceConfiguration;
        $this->transformationHandler = $transformationHandler;
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
        $api = $this->decideApiAndEndpoint($request->getUri());
        $this->registerProxySources($api['apiName']);

        $url = $this->apiLoader->getEndpoint($api['endpoint'], true);
        if (parse_url($url, PHP_URL_SCHEME) === false) {
            $scheme = $request->getScheme();
            $url = $scheme.'://'.$url;
        }
        $response = null;
        try {
            $newRequest = Request::create(
                $url,
                $request->getMethod(),
                array (),
                array (),
                array (),
                array (),
                $request->getContent(false)
            );
            $newRequest->headers->add($request->headers->all());
            $newRequest->query->add($request->query->all());
            $queryString = $request->server->get('QUERY_STRING');
            $newRequest->server->set('QUERY_STRING', $queryString);

            $newRequest = $this->transformationHandler->transformRequest(
                $api['apiName'],
                $api['endpoint'],
                $request,
                $newRequest
            );
            $psrRequest = $this->diactorosFactory->createRequest($newRequest);
            $psrRequest = $psrRequest->withUri($psrRequest->getUri()->withPort(parse_url($url, PHP_URL_PORT)));
            $psrResponse = $this->proxy->forward($psrRequest)->to($this->getHostWithScheme($url));
            $response = $this->httpFoundationFactory->createResponse($psrResponse);
            $this->cleanResponseHeaders($response->headers);
            $this->transformationHandler->transformResponse(
                $api['apiName'],
                $api['endpoint'],
                $response,
                clone $response
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (ServerException $serverException) {
            $response = $serverException->getResponse();
        }

        return $response;
    }

    /**
     * Removes some headers from the thirdparty API's response. These headers get always invalid by graviton's
     * forwarding and should therefore not be delivered to the client.
     *
     * @param HeaderBag $headers The headerbag holding the thirdparty API's response headers
     *
     * @return void
     */
    protected function cleanResponseHeaders(HeaderBag $headers)
    {
        $headers->remove('transfer-encoding'); // Chunked responses get not automatically re-chunked by graviton
        $headers->remove('trailer'); // Only for chunked responses, graviton should re-set this when chunking
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
        $this->registerProxySources($api['apiName']);
        $schema = $this->apiLoader->getEndpointSchema(urldecode($api['endpoint']));
        $schema = $this->transformationHandler->transformSchema(
            $api['apiName'],
            $api['endpoint'],
            $schema,
            clone $schema
        );
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
     * @param string $apiPrefix The prefix of the API
     *
     * @return void
     */
    private function registerProxySources($apiPrefix = '')
    {
        if (array_key_exists('swagger', $this->proxySourceConfiguration)) {
            foreach ($this->proxySourceConfiguration['swagger'] as $config) {
                if ($apiPrefix == $config['prefix']) {
                    $this->apiLoader->setOption($config);
                    return;
                }
            }
        }
        $e = new NotFoundException('No such thirdparty API.');
        $e->setResponse(Response::create());
        throw $e;
    }

    /**
     * get host, scheme and port
     *
     * @param string $url the url
     *
     * @return string
     */
    private function getHostWithScheme($url)
    {
        $components = parse_url($url);
        $host = $components['scheme'].'://'.$components['host'];
        if (!empty($components['port'])) {
            $host .= ':'.$components['port'];
        }

        return $host;
    }
}
