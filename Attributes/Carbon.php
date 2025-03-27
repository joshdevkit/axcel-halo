<?php

namespace Axcel\AxcelCore\Attributes;

use DateTime;
use DateTimeZone;
use Exception;

class Carbon
{
    protected $dateTime;

    public function __construct($date = 'now', $timezone = null)
    {
        $timezone = $timezone ?: env('DEFAULT_TIMEZONE', 'UTC');
        $this->dateTime = new DateTime($date, new DateTimeZone($timezone));
    }

    /**
     * Get the current date and time.
     *
     * @param string|null $timezone
     * @return \App\Helpers\Carbon
     */
    public static function now($timezone = null)
    {
        $carbon = new self('now', $timezone);
        return $carbon->format('Y-m-d H:i:s');
    }

    /**
     * Get today's date.
     *
     * @param string|null $timezone
     * @return \App\Helpers\Carbon
     */
    public static function today($timezone = null)
    {
        return new self('today', $timezone);
    }

    /**
     * Get tomorrow's date.
     *
     * @param string|null $timezone
     * @return \App\Helpers\Carbon
     */
    public static function tomorrow($timezone = null)
    {
        return new self('tomorrow', $timezone);
    }

    /**
     * Get yesterday's date.
     *
     * @param string|null $timezone
     * @return \App\Helpers\Carbon
     */
    public static function yesterday($timezone = null)
    {
        return new self('yesterday', $timezone);
    }

    /**
     * Add days to the current date.
     *
     * @param int $days
     * @return \App\Helpers\Carbon
     */
    public function addDays($days)
    {
        $this->dateTime->modify("+$days days");
        return $this;
    }

    /**
     * Subtract days from the current date.
     *
     * @param int $days
     * @return \App\Helpers\Carbon
     */
    public function subDays($days)
    {
        $this->dateTime->modify("-$days days");
        return $this;
    }

    /**
     * Format the date as a string.
     *
     * @param string $format
     * @return string
     */
    public function format($format = 'Y-m-d H:i:s')
    {
        return $this->dateTime->format($format);
    }

    /**
     * Get the timestamp of the current date.
     *
     * @return int
     */
    public function timestamp()
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Get the difference in days between this date and another.
     *
     * @param \App\Helpers\Carbon $date
     * @return int
     */
    public function diffInDays(Carbon $date)
    {
        $diff = $this->dateTime->diff($date->getDateTime());
        return $diff->days;
    }

    /**
     * Get the underlying DateTime object.
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
