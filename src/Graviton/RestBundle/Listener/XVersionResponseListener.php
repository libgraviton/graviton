<?php

namespace Graviton\RestBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class XVersionResponseListener
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class XVersionResponseListener
{
    const X_VERSION_DEFAULT = "0.1.0-alpha";

    /** @var Filesystem */
    private $fileSystem;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $file;


    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, $file = '')
    {
        $this->logger = $logger;
        $this->file = !empty($file) ? $file : __DIR__ . '/../../../../composer.json';
    }

    /**
     * Adds a X-Version header to the response.
     *
     * @param FilterResponseEvent $event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $event->getResponse();
        $response->headers->set(
            'X-Version',
            $this->extractVersionString($this->file)
        );
    }

    /**
     * Extracts the version information of the current package from the project's composer.json file.
     *
     * @param string $filePath
     *
     * @return string
     */
    private function extractVersionString($filePath)
    {
        $version = self::X_VERSION_DEFAULT;

        if (file_exists($filePath)) {

            $composer = json_decode(file_get_contents($filePath), true);

            if (JSON_ERROR_NONE === json_last_error() && !empty($composer['version'])) {
                $version = $composer['version'];
            } else {
                $this->logger->warning(
                    sprintf(
                        'Unable to extract version from composer.json file: %s (%s)',
                        json_last_error_msg(),
                        json_last_error()
                    )
                );
            }
        }

        return $version;
    }
}
