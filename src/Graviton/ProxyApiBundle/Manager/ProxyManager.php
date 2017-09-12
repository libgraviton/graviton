<?php
/**
 * Database manager and query manager
 */

namespace Graviton\ProxyApiBundle\Manager;

use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ProxyApiBundle\Listener\ProxyExceptionListener;
use Graviton\ProxyApiBundle\Model\ProxyModel;

/**
 * Request manager and service definition start up
 * Build by compiler to be used in ProxyManager
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyManager
{
    /** @var array */
    protected $services = [];

    /**
     * Add services ProxyModel
     *
     * @param ProxyModel $services Injected by Compiler
     * @return void
     */
    public function addServices($services)
    {
        $this->services = $services;
    }

    /**
     * Get available proxy's
     *
     * @return array
     */
    public function getServices()
    {
        return [
            'services' => $this->services,
        ];
    }

    /**
     * @param string $client Name of the Client
     * @return ProxyModel
     * @throws ProxyExceptionListener
     */
    public function getService($client)
    {
        if (array_key_exists($client, $this->services)) {
            return $this->services[$client];
        }
        throw new ProxyExceptionListener(404, sprintf('No proxy found for client: %s', $client));
    }
}
