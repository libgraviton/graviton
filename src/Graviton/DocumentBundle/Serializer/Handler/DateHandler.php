<?php
/**
 * DateHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Service\DateConverter;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * Date handler for JMS serializer
 * This is needed as we allowed the client to pass microseconds in datetime values.
 * This cannot be parsed by the Serializer as it's not always the same format. By using the Rfc3339 class
 * from JsonSchema, we use the same logic as we use in the validation and can accept that date.
 * Note that the main logic from JMS DateHandler is still used..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class DateHandler implements SubscribingHandlerInterface
{

    /**
     * @var \JMS\Serializer\Handler\DateHandler
     */
    private \JMS\Serializer\Handler\DateHandler $baseDateHandler;

    /**
     * DateHandler constructor.
     *
     * @param DateConverter $dateConverter date converter
     */
    public function __construct(private DateConverter $dateConverter)
    {
        $this->baseDateHandler = new BaseDateHandler(
            $dateConverter->getDateFormat(),
            $dateConverter->getTimezone(),
            true
        );
    }

    /**
     * make sure serializer uses us by setting a lower priority
     *
     * @return array methods
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $deserializationTypes = ['DateTime'];
        $serialisationTypes = ['DateTime'];

        foreach (['json', 'xml'] as $format) {
            foreach ($deserializationTypes as $type) {
                $methods[] = [
                    'type' => $type,
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'format' => $format,
                    'priority' => -100
                ];
            }

            foreach ($serialisationTypes as $type) {
                $methods[] = [
                    'type' => $type,
                    'format' => $format,
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'method' => 'serialize' . $type,
                    'priority' => -100
                ];
            }
        }

        return $methods;
    }

    /**
     * serialize datetime from json
     *
     * @param DeserializationVisitorInterface $visitor visitor
     * @param string                          $data    data
     * @param array                           $type    type
     *
     * @return \DateTime|null DateTime instance
     */
    public function deserializeDateTimeFromJson(DeserializationVisitorInterface $visitor, $data, array $type)
    {
        if (null === $data) {
            return null;
        }

        return $this->baseDateHandler->deserializeDateTimeFromJson(
            $visitor,
            $this->dateConverter->getDateTimeStringInFormat($data),
            $type
        );
    }

    /**
     * serialize datetime to json
     *
     * @param SerializationVisitorInterface $visitor visitor
     * @param \DateTime                     $date    data
     * @param array                         $type    type
     * @param Context                       $context context
     *
     * @return string serialized date
     */
    public function serializeDateTime(
        SerializationVisitorInterface $visitor,
        \DateTime $date,
        array $type,
        Context $context
    ) {
        return $visitor->visitString($this->dateConverter->formatDateTime($date), $type, $context);
    }
}
