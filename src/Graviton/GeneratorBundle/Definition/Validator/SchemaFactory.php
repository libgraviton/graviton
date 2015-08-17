<?php
/**
 * SchemaFactory class file
 */

namespace Graviton\GeneratorBundle\Definition\Validator;

use HadesArchitect\JsonSchemaBundle\Uri\UriRetrieverServiceInterface;
use JsonSchema\RefResolver;

/**
 * JSON schema factory
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaFactory
{
    /**
     * @var UriRetrieverServiceInterface
     */
    private $uriRetriever;
    /**
     * @var RefResolver
     */
    private $refResolver;

    /**
     * Constructor
     *
     * @param UriRetrieverServiceInterface $uriRetriever URI retriever
     */
    public function __construct(UriRetrieverServiceInterface $uriRetriever)
    {
        $this->uriRetriever = $uriRetriever;
        $this->refResolver = new RefResolver($this->uriRetriever);
    }

    /**
     * Create JSON schema
     *
     * @param string $uri Schema URI
     * @return object
     */
    public function createSchema($uri)
    {
        $prevDepth = RefResolver::$maxDepth;
        RefResolver::$maxDepth = PHP_INT_MAX;

        $schema = $this->uriRetriever->retrieve($uri);
        $this->refResolver->resolve($schema, $uri);

        RefResolver::$maxDepth = $prevDepth;

        return $schema;
    }
}
