<?php
/**
 * Controller for core/version endpoint
 */

namespace Graviton\CoreBundle\Controller;

use Graviton\RestBundle\Trait\SchemaTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class VersionController
{
    use SchemaTrait;

    /**
     * @param array $versionInformation version information
     */
    public function __construct(private array $versionInformation)
    {
    }

    /**
     * Returns all version numbers
     *
     * @return Response $response Response with result or error
     */
    public function versionsAction() : Response
    {
        return new JsonResponse(
            [
                'versions' => $this->versionInformation
            ]
        );
    }

    /**
     * Returns schema
     *
     * @param string  $format  format
     * @param Request $request request
     *
     * @return Response $response Response with result or error
     */
    public function versionsSchemaAction($format, Request $request)
    {
        return $this->getResponseFromSchemaFile(
            __DIR__.'/../Resources/config/schema/openapi.json',
            $format
        );
    }
}
