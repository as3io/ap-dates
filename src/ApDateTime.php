<?php

namespace As3\ApDates;

use \DateTime;
use \DateTimeZone;

/**
 * Provides Associated Press (AP) formatting of the standard PHP DateTime object.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ApDateTime extends DateTime
{
    /**
     * @var ApFormatter
     */
    private $formatter;

    /**
     * Formats this DateTime instance using the AP formatting rules
     *
     * @param   string|null     $format
     * @return  string
     */
    public function apFormat($format = null)
    {
        $this->initFormatter();
        return $this->formatter->format($this, $format);
    }

    /**
     * Formats this DateTime instance compared to the provided until date.
     *
     * @param   DateTime    $until
     * @param   string|null $format
     * @return  string
     */
    public function apFormatUntil(DateTime $until, $format = null)
    {
        $this->initFormatter();
        return $this->formatter->formatRange($this, $until, $format);
    }

    /**
     * Gets the low-level date formatter instance.
     *
     * @return  ApFormatter
     */
    public function getFormatter()
    {
        $this->initFormatter();
        return $this->formatter;
    }

    /**
     * Initializes the date formatter instance.
     *
     * @return  self
     */
    private function initFormatter()
    {
        if (null === $this->formatter) {
            $this->formatter = new ApFormatter();
        }
        return $this;
    }
}
