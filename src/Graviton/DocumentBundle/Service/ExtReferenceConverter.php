<?php
/**
 * ExtReferenceConverter class file
 */

namespace Graviton\DocumentBundle\Service;

use Graviton\DocumentBundle\Entity\ExtReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extref converter
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceConverter
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor
     *
     * @param RouterInterface $router  Router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * return the extref from URL
     *
     * @param string $url Extref URL
     * @return ExtReference
     * @throws \InvalidArgumentException
     */
    public function getExtReference($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (empty($path)) {
            throw new \InvalidArgumentException(sprintf('URL %s', $url));
        }

        try {
            $previousContext = $this->router->getContext();
            $this->router->setContext(RequestContext::fromUri($path));

            $route = $this->router->matchRequest(Request::create($path));

            $this->router->setContext($previousContext);

            if (is_array($route) && isset($route['collection']) && isset($route['id'])) {
                return ExtReference::create($route['collection'], $route['id']);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Error while determining route for %s', $path),
                0,
                $e
            );
        }

        throw new \InvalidArgumentException(sprintf('Could not read URL %s', $url));
    }

    /**
     * return the URL from extref
     *
     * @param ExtReference $extReference Extref
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUrl(ExtReference $extReference)
    {
        return $this->router->generate(
            $extReference->getRef().'.get',
            ['id' => $extReference->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
