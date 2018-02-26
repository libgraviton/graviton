<?php
/**
 * DateHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JsonSchema\Rfc3339;

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
     * @var string
     */
    private $defaultFormat;

    /**
     * @var string
     */
    private $defaultTimezone;

    /**
     * DateHandler constructor.
     *
     * @param string $defaultFormat   configured default format
     * @param string $defaultTimezone default timezone
     * @param bool   $xmlCData        xml data
     */
    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC', $xmlCData = true)
    {
        $this->defaultFormat = $defaultFormat;
        $this->defaultTimezone = $defaultTimezone;
        parent::__construct($defaultFormat, $defaultTimezone, $xmlCData);
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

        $dt = Rfc3339::createFromString($data);

        return parent::deserializeDateTimeFromJson($visitor, $dt->format($this->defaultFormat), $type);
    }
}
