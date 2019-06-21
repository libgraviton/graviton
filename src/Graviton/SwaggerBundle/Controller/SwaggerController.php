<?php
/**
 * controller for rendering our swagger spec
 */

namespace Graviton\SwaggerBundle\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

/**
 * SwaggerController
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SwaggerController
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param Finder $finder  symfony/finder instance
     * @param string $rootDir symfomy root dir
     */
    public function __construct(Finder $finder, $rootDir)
    {
        $this->finder = $finder;
        $this->rootDir = $rootDir;
    }

    /**
     * @throws \LogicException
     *
     * @return Response Response with result or error
     */
    public function swaggerAction()
    {
        $this->finder->files()->in($this->rootDir)->depth(0)->name('swagger.json');

        if ($this->finder->count() != 1) {
            throw new \LogicException('Failed to find a generated swagger file');
        }

        foreach ($this->finder as $file) {
            return new Response(
                $file->getContents(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/json'
                ],
                false
            );
        }
    }
}
