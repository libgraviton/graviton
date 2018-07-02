<?php
/**
 * DateHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Service\DateConverter;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\VisitorInterface;

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
class DateHandler extends BaseDateHandler
{

    /**
     * @var DateConverter
     */
    private $dateConverter;

    /**
     * DateHandler constructor.
     *
     * @param DateConverter $dateConverter date converter
     */
    public function __construct(DateConverter $dateConverter)
    {
        $this->dateConverter = $dateConverter;
        parent::__construct($dateConverter->getDateFormat(), $dateConverter->getTimezone(), true);
    }

    /**
     * make sure serializer uses us by setting a lower priority
     *
     * @return array methods
     */
    public static function getSubscribingMethods()
    {
        $methods = array_map(
            function ($item) {
                $item['priority'] = -100;
                return $item;
            },
            parent::getSubscribingMethods()
        );

        return $methods;
    }

    /**
     * serialize datetime from json
     *
     * @param JsonDeserializationVisitor $visitor visitor
     * @param string                     $data    data
     * @param array                      $type    type
     *
     * @return \DateTime|null DateTime instance
     */
    public function deserializeDateTimeFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        if (null === $data) {
            return null;
        }

        return parent::deserializeDateTimeFromJson(
            $visitor,
            $this->dateConverter->getDateTimeStringInFormat($data),
            $type
        );
    }

    /**
     * serialize datetime to json
     *
     * @param VisitorInterface $visitor visitor
     * @param \DateTime        $date    data
     * @param array            $type    type
     * @param Context          $context context
     *
     * @return string serialized date
     */
    public function serializeDateTime(VisitorInterface $visitor, \DateTime $date, array $type, Context $context)
    {
        return $visitor->visitString($this->dateConverter->formatDateTime($date), $type, $context);
    }
}
