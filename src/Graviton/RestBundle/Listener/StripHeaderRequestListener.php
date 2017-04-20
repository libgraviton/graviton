<?php
/**
 * ResponseListener for removing named header fields.
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StripHeaderRequestListener
 *
 * @package Graviton\RestBundle\Listener
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class StripHeaderRequestListener
{
    /** @var array  */
    private $fieldnames = [];

    /**
     * StripHeaderRequestListener constructor.
     *
     * @param array $fieldnames  Name of header fields to be stripped off the request.
     */
    public function __construct(array $fieldnames = [])
    {
        $this->fieldnames = $fieldnames;
    }

    /**
     * Validate the json input to prevent errors in the following components
     *
     * @param RestEvent $event Event
     *
     * @return void
     */
    public function onKernelRequest(RestEvent $event)
    {
        $request = $event->getRequest();

        foreach ($this->fieldnames as $fieldname) {
            $this->removeHeaderField($request, $fieldname);
        }
    }

    /**
     * @param $request
     */
    private function removeHeaderField(Request $request, $fieldname)
    {
        if ($request->headers->has($fieldname)) {
            $request->headers->remove($fieldname);
        }
    }
}
