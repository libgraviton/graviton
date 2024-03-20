<?php
/**
 * event object when the homepage is rendered
 */

namespace Graviton\CoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class HomepageRenderEvent extends Event
{
    /**
     * our event name
     *
     * @var string
     */
    const string EVENT_NAME = 'homepage.render';

    /**
     * added routes
     *
     * @var array
     */
    private array $addedRoutes = [];

    /**
     * add a route to the homepage
     *
     * @param string $url        relative (to root) url to the service
     * @param string $schemaJson json schema url
     * @param string $schemaYaml yaml schema url
     *
     * @return void
     */
    public function addRoute(string $url, string $schemaJson, string $schemaYaml) : void
    {
        $this->addedRoutes[] = [
            '$ref' => $this->normalizeRelativeUrl($url),
            'api-docs' => [
                'json' => ['$ref' => $this->normalizeRelativeUrl($schemaJson)],
                'yaml' => ['$ref' => $this->normalizeRelativeUrl($schemaYaml)]
            ]
        ];
    }

    /**
     * remove possibly leading slash from url
     *
     * @param string $url url
     *
     * @return string url
     */
    private function normalizeRelativeUrl(string $url): string
    {
        if (str_starts_with($url, '/')) {
            return substr($url, 1);
        }
        return $url;
    }

    /**
     * returns the routes
     *
     * @return array routes
     */
    public function getRoutes() : array
    {
        return $this->addedRoutes;
    }
}
