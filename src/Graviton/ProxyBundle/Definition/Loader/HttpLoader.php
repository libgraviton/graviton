<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Guzzle\Http\Client;
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
     * @param string $input url
     *
     * @return boolean
     */
    public function supports($input)
    {
        $retVal = false;
        $error = $this->validator->validate($input, [new Url()]);
        if (count($error) == 0) {
            $retVal = true;
        }

        return $retVal;
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
            $response = $request->send();
            $content = $response->getBody(true);
            if ($this->strategy->supports($content)) {
                $retVal = $this->strategy->process($content);
            }
        }

        return $retVal;
    }
}
