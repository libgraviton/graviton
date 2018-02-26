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
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
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

        /**
         * @var \stdClass $swagger
         */
        $swagger = $this->decodeJson($input);
        if (is_object($swagger)) {
            $this->document->setDocument($swagger);
            $this->setBaseValues($apiDef);

            $operations = $this->document->getOperationsById();
            foreach ($operations as $service) {
                $path = $service->getPath();

                if (in_array(strtolower($service->getMethod()), ['delete', 'patch']) || $apiDef->hasEndpoint($path)) {
                    continue;
                }
                $apiDef->addEndpoint($path);
                $apiDef->addSchema(
                    $path,
                    $this->getServiceSchema($service)
                );
                $apiDef->setOrigin($this->document);
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
        /**
         * @var array $swagger
         */
        $swagger = $this->decodeJson($input, true);

        if (empty($swagger)) {
            return false;
        }

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
     */
    private function decodeJson($input, $assoc = false)
    {
        $input = trim($input);

        return json_decode($input, $assoc);
    }

    /**
     * set base values
     *
     * @param ApiDefinition $apiDef API definition
     *
     * @return void
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
        $operation = $service->getOperation();
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
                        $ref = $parameter->getDocumentObjectProperty('schema', Reference::class)->getDocument();
                        $schema = $this->resolveSchema($ref);
                        break;
                    }
                }
                break;
            case "get":
                try {
                    $response = $operation->getResponses()->getHttpStatusCode(200);
                } catch (MissingDocumentPropertyException $e) {
                    // no response with status code 200 is defined
                    break;
                }
                $schema = $this->resolveSchema($response->getSchema()->getDocument());
                break;
        }

        return $schema;
    }

    /**
     * resolve schema
     *
     * @param \stdClass $reference reference
     *
     * @return \stdClass
     */
    private function resolveSchema($reference)
    {
        $schema = $reference;
        if (property_exists($reference, '$ref')) {
            $schemaResolver = $this->document->getSchemaResolver();
            $ref = new Reference($reference);
            $schema = $schemaResolver->resolveReference($ref)->getDocument();
        } elseif ($reference->type === 'array' && !empty($reference->items)) {
            $schema->items = $this->resolveSchema($reference->items);
        }

        // resolve properties
        if (!empty($schema->properties)) {
            $properties = (array) $schema->properties;
            foreach ($properties as $name => $property) {
                if (isset($property->type)
                    && $property->type === 'array'
                    && isset($property->items)
                    && property_exists($property->items, '$ref')
                    && !isset($schema->properties->$name->items)) {
                    $schema->properties->$name->items = $this->resolveSchema($property->items);
                } elseif (property_exists($property, '$ref') && !isset($schema->properties->$name)) {
                    $schema->properties->$name = $this->resolveSchema($property);
                }
            }
        }

        return $schema;
    }

    /**
     * Sets the destination host for the api definition.
     *
     * @param ApiDefinition $apiDef Configuration for the swagger api to be recognized.
     *
     * @return void
     */
    private function registerHost(ApiDefinition $apiDef)
    {
        try {
            $host = $this->document->getHost();
        } catch (MissingDocumentPropertyException $e) {
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
}
