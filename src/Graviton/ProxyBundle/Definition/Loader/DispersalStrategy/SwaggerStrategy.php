<?php
/**
 * SwaggerStrategy
 */

namespace Graviton\ProxyBundle\Definition\Loader\DispersalStrategy;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Swagger\Document;
use Swagger\Exception\MissingDocumentPropertyException;
use Swagger\Object\AbstractObject;
use Swagger\Object\Parameter;
use Swagger\Object\Reference;
use Swagger\OperationReference;

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
     *
     */
    private $document;

    /**
     * constructor
     *
     * @param Document $document Swagger document parser
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

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
            $this->document->setDocument($swagger);
            $this->setBaseValues($apiDef);

            $operations = $this->document->getOperationsById();
            foreach ($operations as $name => $service) {
                $path = $this->normalizePath($service->getPath());

                if ($apiDef->hasEndpoint($path)) {
                    continue;
                }
                $apiDef->addEndpoint($path);
                $apiDef->addSchema(
                    $path,
                    $this->getServiceSchema($service)
                );
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

        $mandatoryFields = ['swagger', 'info', 'paths', 'version', 'title', 'definitions'];
        $fields = array_merge(array_keys($swagger), array_keys($swagger['info']));
        $intersect = array_intersect($mandatoryFields, $fields);

        // every mandatory field was found in provided json definition.
        return empty(array_diff($mandatoryFields, $intersect));
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
     *
     * @return void
     *
     */
    private function setBaseValues(ApiDefinition $apiDef)
    {
        $this->registerHost($apiDef);
        $basePath = $this->document->getBasePath();
        if (isset($basePath)) {
            $apiDef->setBasePath($basePath);
        }
    }

    /**
     * get the schema
     *
     * @param OperationReference $service service endpoint
     *
     * @return \stdClass
     */
    private function getServiceSchema($service)
    {
        $schemaResolver = $this->document->getSchemaResolver();
        $operation = $service->getOperation();
        $ref = new \stdClass();
        $schema = new \stdClass();
        switch (strtolower($service->getMethod())) {
            case "post":
            case "put":
                try {
                    $parameters = $operation->getDocumentObjectProperty('parameters', Parameter\Body::class, true);
                } catch (MissingDocumentPropertyException $e) {
                    // request has no params
                    break;
                }
                foreach ($parameters as $parameter) {
                    /**
                     * there is no schema information available, if $action->parameters[0]->in != 'body'
                     *
                     * @link http://swagger.io/specification/#parameterObject
                     */
                    if ($parameter instanceof Parameter\Body && $parameter->getIn() === 'body') {
                        $ref = $parameter->getDocumentObjectProperty('schema', Reference::class, true);
                    }
                }
                break;
            case "get":
                try {
                    $response = $operation->getResponses()->getHttpStatusCode(200);
                } catch (MissingDocumentPropertyException $e) {
                    // no response is defined
                    break;
                }
                $ref = $response->getSchema();
                break;
        }

        if ($ref instanceof AbstractObject
            && !empty($ref->getDocument()->type)
            && $ref->getDocument()->type === 'array') {
            $ref = new Reference($ref->getDocument()->items);
        }
        if ($ref instanceof Reference) {
            $schema = $schemaResolver->resolveReference($ref)->getDocument();
        }

        return $schema;
    }

    /**
     * Sets the destination host for the api definition.
     *
     * @param ApiDefinition $apiDef  Configuration for the swagger api to be recognized.
     *
     * @return void
     */
    private function registerHost(ApiDefinition $apiDef)
    {
        $host = $this->document->getHost();
        if (!isset($host)) {
            $host = $this->fallbackData['host'];
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
