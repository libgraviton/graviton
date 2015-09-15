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
     * process data
     *
     * @param null|string $input input
     *
     * @return ApiDefinition
     */
    public function process($input)
    {
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
                if ($definitionRef != null) {
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
     * @param string $input
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
     * @param string $input
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
    private function setBaseValues(ApiDefinition &$apiDef, \stdClass $swagger)
    {
        $apiDef->setHost($swagger->host);
        if (isset($swagger->basePath)) {
            $apiDef->setBasePath($swagger->basePath);
        }
    }

    /**
     * get the name of definition field for the schema
     *
     * @param \stdClass $endpoint endpoint
     *
     * @return string|null
     */
    private function getEndpointDefinition($endpoint)
    {
        $refName = "\$ref";
        $ref = null;
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
}
