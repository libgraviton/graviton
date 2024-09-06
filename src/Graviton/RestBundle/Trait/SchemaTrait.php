<?php
/**
 * trait for simple schema controller stuff
 */
namespace Graviton\RestBundle\Trait;

use Graviton\CommonBundle\CommonUtils;
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

    private array $schemaVersionInformation = [];

    /**
     * set version info
     *
     * @param array $information information
     *
     * @return void
     */
    public function setVersionInformation(array $information) : void
    {
        $this->schemaVersionInformation = $information;
    }

    /**
     * gets the response
     *
     * @param string  $filepath     filepath
     * @param string  $format       format
     * @param ?string $excludePaths paths to exclude
     * @param ?string $includePaths paths to include
     *
     * @return Response response
     */
    public function getResponseFromSchemaFile(
        string $filepath,
        string $format,
        ?string $excludePaths = null,
        ?string $includePaths = null
    ) : Response {
        return $this->getResponseFromSchema(
            \json_decode(file_get_contents($filepath), true),
            $format,
            $excludePaths,
            $includePaths
        );
    }

    /**
     * return the response from a schema
     *
     * @param array   $schema       schema
     * @param string  $format       format
     * @param ?string $excludePaths paths to exclude
     * @param ?string $includePaths paths to include
     *
     * @return Response response
     */
    public function getResponseFromSchema(
        array $schema,
        string $format,
        ?string $excludePaths = null,
        ?string $includePaths = null
    ) : Response {

        // inject version
        if (!empty($this->schemaVersionInformation) && !empty($schema['info'])) {
            $schema['info']['version'] = $this->schemaVersionInformation['self'];
        }

        // excludes?
        if (!is_null($excludePaths)) {
            $definedSet = array_keys($schema['paths']);
            foreach ($definedSet as $key => $path) {
                if (CommonUtils::subjectMatchesStringWildcards($excludePaths, $path)) {
                    unset($definedSet[$key]);
                }
            }

            // now, do we have some includes? we only do this when there are excludes first!
            if (!is_null($includePaths)) {
                foreach ($schema['paths'] as $key => $path) {
                    if (CommonUtils::subjectMatchesStringWildcards($includePaths, $key)) {
                        // add id
                        $definedSet[] = $key;
                    }
                }
            }

            // cleanup
            natsort($definedSet);

            $newPaths = [];
            foreach ($definedSet as $setMember) {
                $newPaths[$setMember] = $schema['paths'][$setMember];
            }

            $schema['paths'] = $newPaths;

            // also filter entities!
            $fullAsJson = json_encode($schema, JSON_UNESCAPED_SLASHES);
            $pattern = '/#\/components\/schemas\/([a-zA-Z0-9\-_]*)/m';
            preg_match_all($pattern, $fullAsJson, $matches);

            if (!empty($matches[1]) && !empty($schema['components']['schemas'])) {
                $includedEntities = array_unique($matches[1]);
                foreach ($schema['components']['schemas'] as $key => $content) {
                    if (!in_array($key, $includedEntities)) {
                        unset($schema['components']['schemas'][$key]);
                    }
                }
            }
        }

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
