<?php
/**
 * event object when the homepage is rendered
 */

namespace Graviton\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class HomepageRenderEvent extends Event
{
    /**
     * our event name
     *
     * @var string
     */
    const EVENT_NAME = 'homepage.render';

    /**
     * added routes
     *
     * @var array
     */
    private $addedRoutes = [];

    /**
     * add a route to the homepage
     *
     * @param string $url       relative (to root) url to the service
     * @param string $schemaUrl relative (to root) url to the schema
     *
     * @return void
     */
    public function addRoute($url, $schemaUrl)
    {
        $this->addedRoutes[] = [
            '$ref' => $this->normalizeRelativeUrl($url),
            'profile' => $this->normalizeRelativeUrl($schemaUrl)
        ];
    }

    /**
     * remove possibly leading slash from url
     *
     * @param string $url url
     *
     * @return string url
     */
    private function normalizeRelativeUrl($url)
    {
        if (substr($url, 0, 1) == '/') {
            return substr($url, 1);
        }
        return $url;
    }

    /**
     * returns the routes
     *
     * @return array routes
     */
    public function getRoutes()
    {
        return $this->addedRoutes;
    }
}
