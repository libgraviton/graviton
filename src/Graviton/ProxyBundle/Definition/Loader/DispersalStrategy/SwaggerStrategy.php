<?php
/**
 * SwaggerStrategy
 */

namespace Graviton\ProxyBundle\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Symfony\Component\Debug\Exception\ContextErrorException;

/**
 * process a swagger.json file and return an APi definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SwaggerStrategy implements DispersalStrategyInterface
{
    /**
     * @var array
     */
    private $fallbackData = [];

    /**
     * process data
     *
     * @param string $input        JSON information about the swagger service.
     * @param array  $fallbackData Set of information to be registered in case the swagger info is not complete.
     *
     * @return ApiDefinition
     */
    public function process($input, array $fallbackData = [])
    {
        $this->registerFallbackData($fallbackData);

        $apiDef = new ApiDefinition();
        $swagger = $this->decodeJson($input);
        if (is_object($swagger)) {
            $this->setBaseValues($apiDef, $swagger);

            foreach ($swagger->paths as $name => $endpoint) {
                $name = preg_replace("@\/{[a-zA-Z]*\}$@", '', $name);

                if ($apiDef->existEndpoint($name)) {
                    continue;
                }
                $apiDef->addEndpoint($name);

                // Schema
                $definitionRef = $this->getEndpointDefinition($endpoint);
                if (! empty($definitionRef)) {
                    list (, $defNode, $defName) = explode('/', $definitionRef);
                    $schema = $swagger->$defNode->$defName;
                    $apiDef->addSchema($name, $schema);
                } else {
                    $apiDef->addSchema($name, new \stdClass());
                }
            }
        }

        return $apiDef;
    }

    /**
     * is input data valid json
     *
     * @param string $input json string
     *
     * @return boolean
     */
    public function supports($input)
    {
        $this->decodeJson($input);

        // check if error occurred
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * decode a json string
     *
     * @param string $input json string
     *
     * @return \stdClass|null
     *
     */
    private function decodeJson($input)
    {
        $input = trim($input);

        return json_decode($input);
    }

    /**
     * set base values
     *
     * @param ApiDefinition $apiDef  API definition
     * @param \stdClass     $swagger swagger object
     *
     * @return void
     *
     */
    private function setBaseValues(ApiDefinition $apiDef, \stdClass $swagger)
    {
        $this->registerHost($apiDef, $swagger);

        if (isset($swagger->basePath)) {
            $apiDef->setBasePath($swagger->basePath);
        }
    }

    /**
     * get the name of definition field for the schema
     *
     * @param \stdClass $endpoint endpoint
     *
     * @return string
     */
    private function getEndpointDefinition($endpoint)
    {
        $refName = "\$ref";
        $ref = '';
        foreach ($endpoint as $actionName => $action) {
            try {
                switch ($actionName) {
                    case "post":
                    case "put":
                        $ref = $action->parameters[0]->schema->$refName;
                        break 2;
                    case "get":
                        $statusCode = 200;
                        $ref = $action->responses->schema->$statusCode->items->$refName;
                        break 2;
                    default:
                        continue;
                        break;
                }
            } catch (ContextErrorException $e) {
                continue;
            }
        }

        return $ref;
    }

    /**
     * @param ApiDefinition $apiDef
     * @param \stdClass     $swagger
     */
    private function registerHost(ApiDefinition $apiDef, \stdClass $swagger)
    {
        $host = $this->fallbackData['host'];

        if (isset($swagger->host)) {
            $host = $swagger->host;
        }

        $apiDef->setHost($host);
    }

    /**
     * @param array $fallbackData
     */
    private function registerFallbackData(array $fallbackData)
    {
        if (!array_key_exists('host', $fallbackData)) {
            throw new \RuntimeException('Missing mandatory key (host) in fallback data set.');
        }

        $this->fallbackData = $fallbackData;
    }
}
