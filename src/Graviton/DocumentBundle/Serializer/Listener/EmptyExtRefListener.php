<?php
/**
 * see that empty extrefs get rendered as type Empty, ie as null
 */

namespace Graviton\DocumentBundle\Serializer\Listener;

use Graviton\DocumentBundle\Document\ExtRefHoldingDocumentInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;

/**
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmptyExtRefListener
{

    /**
     * if is empty extref object, divert serializing to type Empty, which creates a null instead of empty object
     *
     * @param PreSerializeEvent $event event
     *
     * @return void
     * @throws \Exception
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();
        if ($object instanceof ExtRefHoldingDocumentInterface && $object->isEmptyExtRefObject() === true) {
            $event->setType('Empty', [$object]);
        }
    }
}
