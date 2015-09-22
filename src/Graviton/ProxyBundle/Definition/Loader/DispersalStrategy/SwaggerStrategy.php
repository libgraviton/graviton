<?php
/**
 * SwaggerStrategy
 */

namespace Graviton\ProxyBundle\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Exception\SchemaException;

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
        /** @var \stdClass $swagger */
        $swagger = $this->decodeJson($input);
        if (is_object($swagger)) {
            $this->setBaseValues($apiDef, $swagger);

            foreach ($swagger->paths as $name => $endpoint) {
                $name = $this->normalizePath($name);

                if ($apiDef->hasEndpoint($name)) {
                    continue;
                }
                $apiDef->addEndpoint($name);

                // Schema
                $definitionRef = $this->getEndpointDefinition($endpoint);
                if (!empty($definitionRef)) {
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
        /** @var array $swagger */
        $swagger = $this->decodeJson($input, true);

        $mandatoryFields = ['swagger', 'info', 'paths', 'version', 'title', 'definition'];
        $fields = array_merge(array_keys($swagger), array_keys($swagger['info']));
        $intersect = array_intersect($mandatoryFields, $fields);

        // every mandatory field was found in provided json definition.
        return empty(array_diff($intersect, $mandatoryFields));
    }

    /**
     * decode a json string
     *
     * @param string $input json string
     * @param bool   $assoc Force the encoded result to be a hash.
     *
     * @return array|\stdClass
     *
     */
    private function decodeJson($input, $assoc = false)
    {
        $input = trim($input);

        return json_decode($input, $assoc);
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
                        /**
                         * there is no schema information available, if $action->parameters[0]->in != 'body'
                         *
                         * @link http://swagger.io/specification/#parameterObject
                         */
                        if ('body' === $action->parameters[0]->in) {
                            $ref = $this->extractReferenceDefinition(
                                (array) $action->parameters[0]->schema,
                                $refName
                            );
                            break 2;
                        }
                        continue 2;
                    case "get":
                        $statusCode = 200;
                        if (!empty($action->responses->$statusCode)) {
                            $ref = $this->extractReferenceDefinition(
                                (array) $action->responses->$statusCode->schema,
                                $refName
                            );
                            break 2;
                        }
                        continue 2;
                    default:
                        continue;
                }
            } catch (SchemaException $e) {
                continue;
            }
        }

        return $ref;
    }

    /**
     * Sets the destination host for the api definition.
     *
     * @param ApiDefinition $apiDef  Configuration for the swagger api to be recognized.
     * @param \stdClass     $swagger Swagger configuration to be parsed.
     *
     * @return void
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
     * Set of information to be used as default if not defined by the swagger configuration.
     *
     * @param array $fallbackData Set of default information (e.g. host)
     *
     * @return void
     */
    private function registerFallbackData(array $fallbackData)
    {
        if (!array_key_exists('host', $fallbackData)) {
            throw new \RuntimeException('Missing mandatory key (host) in fallback data set.');
        }

        $this->fallbackData = $fallbackData;
    }

    /**
     * Finds the definition of referred entities in the schema definition.
     *
     * @param array  $schema              Api schema to be scanned.
     * @param string $referenceIdentifier Key of the identifier used to id a reference.
     *
     * @throws \Exception
     * @return string
     */
    private function extractReferenceDefinition(array $schema, $referenceIdentifier)
    {
        if (array_key_exists($referenceIdentifier, $schema)) {
            return $schema[$referenceIdentifier];
        } else {
            if (array_key_exists('items', $schema) && array_key_exists($referenceIdentifier, $schema['items'])) {
                return $schema['items']->$referenceIdentifier;
            }
        }

        throw new SchemaException(
            sprintf('Reference identifier (%s) was not available in provided schema!', $referenceIdentifier)
        );
    }

    /**
     * Normalizes the provided path.
     *
     * The idea is to consolidate endpoints for GET requests.
     *
     * <code>
     *   /my/path/      » /my/path
     *   /my/path/{id}  » /my/path
     * </code>
     *
     * @param string $path path to be normalized
     *
     * @return string
     *
     * @todo: determine how to treat endpoints with a variable within the path: /my/path/{id}/special
     */
    private function normalizePath($path)
    {
        return preg_replace('@\/(\{[a-zA-Z]*\})?$@', '', $path);
    }
}
