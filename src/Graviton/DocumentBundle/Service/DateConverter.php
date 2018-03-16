<?php
/**
 * DateConverter class file
 */

namespace Graviton\DocumentBundle\Service;

use JsonSchema\Rfc3339;

/**
 * Date converter
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DateConverter
{

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var string
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param string $dateFormat date format
     * @param string $timezone   timezone
     */
    public function __construct($dateFormat, $timezone)
    {
        $this->dateFormat = $dateFormat;
        $this->timezone = $timezone;
    }

    /**
     * get DateFormat
     *
     * @return string DateFormat
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * get Timezone
     *
     * @return string Timezone
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Returns a DateTime instance from a string representation
     *
     * @param string $data string Rfc3339 date
     *
     * @return \DateTime datetime
     */
    public function getDateTimeFromString($data)
    {
        return Rfc3339::createFromString($data);
    }

    /**
     * formats a DateTime to the defined default format
     *
     * @param \DateTime $dateTime datetime
     *
     * @return string formatted date
     */
    public function formatDateTime(\DateTime $dateTime)
    {
        return $dateTime->format($this->dateFormat);
    }

    /**
     * accepts a Rfc3339 datetime string and converts it to the defined default format
     *
     * @param string $data Rfc3339 datetime
     *
     * @return string date in configured format
     */
    public function getDateTimeStringInFormat($data)
    {
        return $this->formatDateTime(
            $this->getDateTimeFromString($data)
        );
    }
}
