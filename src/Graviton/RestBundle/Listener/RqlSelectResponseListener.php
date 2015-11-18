<?php
/**
 * RqlSelectResponseListener class file
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RestBundle\Utils\ObjectSlicer;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Xiag\Rql\Parser\Query;

/**
 * FilterResponseListener for applying RQL "select" statement
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlSelectResponseListener
{
    /**
     * @var ObjectSlicer
     */
    private $objectSlicer;

    /**
     * Constructor
     *
     * @param ObjectSlicer $objectSlicer Object slicer
     */
    public function __construct(ObjectSlicer $objectSlicer)
    {
        $this->objectSlicer = $objectSlicer;
    }

    /**
     * Apply RQL select
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $rqlQuery = $event->getRequest()->attributes->get('rqlQuery');
        if (!$rqlQuery instanceof Query) {
            return;
        }
        if ($rqlQuery->getSelect() === null) {
            return;
        }

        $response = $event->getResponse();
        if ($response->getStatusCode() !== 200) {
            return;
        }

        $documents = json_decode($response->getContent());
        if (!is_object($documents) && !is_array($documents)) {
            return;
        }

        $documents = $this->objectSlicer->sliceMulti(
            $documents,
            $rqlQuery->getSelect()->getFields()
        );
        $response->setContent(json_encode($documents));
    }
}
