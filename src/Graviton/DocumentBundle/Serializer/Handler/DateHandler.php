<?php
/**
 * DateHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use Graviton\DocumentBundle\Service\DateConverter;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;

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
}
