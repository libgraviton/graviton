<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * load a file over http and process the data
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HttpLoader implements LoaderInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var DispersalStrategyInterface
     */
    private $strategy;

    /**
     * constructor
     *
     * @param ValidatorInterface $validator validator
     * @param Client             $client    http client
     */
    public function __construct(ValidatorInterface $validator, Client $client)
    {
        $this->validator = $validator;
        $this->client = $client;
    }

    /**
     * @inheritDoc
     *
     * @param DispersalStrategyInterface $strategy dispersal strategy
     *
     * @return void
     */
    public function setDispersalStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * check if the url is valid
     *
     * @param string $url url
     *
     * @return boolean
     */
    public function supports($url)
    {
        $error = $this->validator->validate($url, [new Url()]);

        return 0 === count($error);
    }

    /**
     * @inheritDoc
     *
     * @param string $input url
     *
     * @return ApiDefinition
     */
    public function load($input)
    {
        $retVal = null;
        if (isset($this->strategy)) {
            $request = $this->client->get($input);

            try {
                $response = $request->send();
            } catch (\Guzzle\Http\Exception\CurlException $e) {
                throw new HttpException(
                    Response::HTTP_BAD_GATEWAY,
                    $e->getError(),
                    $e,
                    $e->getRequest()->getHeaders()->toArray(),
                    $e->getCode()
                );
            }

            $content = $response->getBody(true);
            if ($this->strategy->supports($content)) {
                // store current host (name or ip) serving the API. This MUST be the host only and does not include the
                // scheme nor sub-paths. It MAY include a port. If the host is not included, the host serving the
                // documentation is to be used (including the port)
                $fallbackHost = array();
                $fallbackHost['host'] = sprintf(
                    '%s://%s:%d',
                    $request->getScheme(),
                    $request->getHost(),
                    $request->getPort()
                );

                $retVal = $this->strategy->process($content, $fallbackHost);
            }
        }

        return $retVal;
    }
}
