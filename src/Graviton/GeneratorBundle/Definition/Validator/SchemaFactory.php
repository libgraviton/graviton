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
     * @var RefResolver
     */
    private $refResolver;

    /**
     * Constructor
     *
     * @param RefResolver $resolver Ref resolver
     */
    public function __construct(RefResolver $resolver)
    {
        $this->refResolver = $resolver;
    }

    /**
     * Create JSON schema
     *
     * @param string $uri Schema URI
     * @return object
     */
    public function createSchema($uri)
    {
        $resolver = $this->refResolver;

        $prevDepth = $resolver::$maxDepth;
        $resolver::$maxDepth = PHP_INT_MAX;

        $schema = $resolver->getUriRetriever()->retrieve($uri);
        $resolver->resolve($schema, $uri);

        $resolver::$maxDepth = $prevDepth;

        return $schema;
    }
}
