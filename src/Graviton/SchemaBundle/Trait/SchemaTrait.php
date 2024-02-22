<?php
/**
 * trait for simple schema controller stuff
 */
namespace Graviton\SchemaBundle\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * SchemaTrait
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
trait SchemaTrait
{

    /**
     * gets the response
     *
     * @param string $filepath filepath
     * @param string $format   format
     *
     * @return Response response
     */
    public function getResponseFromSchemaFile(string $filepath, string $format) : Response
    {
        return $this->getResponseFromSchema(
            \json_decode(file_get_contents($filepath), true),
            $format
        );


    }

    /**
     * return the response from a schema
     *
     * @param array  $schema schema
     * @param string $format format
     *
     * @return Response response
     */
    public function getResponseFromSchema(array $schema, string $format) : Response
    {
        if ($format == 'json') {
            return new JsonResponse($schema, 200, []);
        }

        return new Response(
            Yaml::dump($schema, 30, 2),
            200,
            ['content-type' => 'application/yaml']
        );
    }
}
