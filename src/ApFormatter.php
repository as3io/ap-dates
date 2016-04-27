<?php

namespace As3\ApDates;

use \DateTime;

/**
 * Handles formatting of PHP DateTime objects into Associated Press (AP) formats.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class ApFormatter
{
    /**
     * Supported format strings.
     *
     * @var array
     */
    private $formats = [
        'c' => 'century',
        'x' => 'decade',
        'l' => 'longdecade',
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'w' => 'dayofweek',
        't' => 'time',
    ];

    /**
     * The formatting options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Constructor.
     * Sets the initial options.
     *
     * @param   string|null     $format
     */
    public function __construct($format = null)
    {
        $this->stringFormat($format);
    }

    /**
     * Sets the century formating option.
     * Setting this option will reset all other options.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function century($enable = true)
    {
        return $this->option('century', $enable);
    }

    /**
     * Sets the day formating option.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function day($enable = true)
    {
        return $this->option('day', $enable);
    }

    /**
     * Sets the dayofweek formating option.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function dayofweek($enable = true)
    {
        return $this->option('dayofweek', $enable);
    }

    /**
     * Sets the decade formating option.
     * Setting this option will reset all other options.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function decade($enable = true)
    {
        return $this->option('decade', $enable);
    }

    /**
     * Formats a DateTime object based on the formatter settings.
     *
     * @param   DateTime    $date   The date to format from.
     * @param   string|null $format The (optional) string format.
     * @return  string
     */
    public function format(DateTime $date, $format = null)
    {
        if (!empty($format)) {
            $this->stringFormat($format);
        }

        if (true === $this->options['century']) {
            return $this->formatCentury($date);
        }
        if (true === $this->options['decade']) {
            return $this->formatDecade($date);
        }
        if (true === $this->options['longdecade']) {
            return $this->formatDecade($date, true);
        }
        return $this->formatDate($date);
    }

    /**
     * Formats a date range.
     *
     * @param   DateTime    $start
     * @param   DateTime    $end
     * @param   string|null $format
     * @return  string
     * @throws  \InvalidArgumentException
     */
    public function formatRange(DateTime $start, DateTime $end, $format = null)
    {
        if ($end < $start) {
            throw new \InvalidArgumentException('The end date cannot be before the start date.');
        }
        $startFormat = $this->format($start, $format);
        $endFormat = $this->format($end, $format);

        if ($startFormat === $endFormat) {
            return $startFormat;
        }

        if (true === $this->options['time'] && $start->format('Y-m-d') === $end->format('Y-m-d')) {
            // Time is display and the dates are on the same day.
            return $this->handleSameDayRange($start, $end);
        }
        return sprintf('%s to %s', $startFormat, $endFormat);
    }

    /**
     * Sets the longdecade formating option.
     * Setting this option will reset all other options.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function longdecade($enable = true)
    {
        return $this->option('longdecade', $enable);
    }

    /**
     * Sets the month formating option.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function month($enable = true)
    {
        return $this->option('month', $enable);
    }

    /**
     * Resets all formatting options.
     *
     * @return  self
     */
    public function reset()
    {
        $this->options = [
            'century'   => false,
            'decade'    => false,
            'longdecade'=> false,
            'year'      => false,
            'month'     => false,
            'day'       => false,
            'dayofweek' => false,
            'time'      => false,
        ];
        return $this;
    }

    /**
     * Sets the date format via a string, such as 'ymd.'
     * If left empty, a sensible default will be set.
     * Will reset any currently active format.
     *
     * @param   string  $format
     * @return  self
     */
    public function stringFormat($format)
    {
        $this->reset();
        $format = (String) $format;
        if (empty($format)) {
            $this->year()->month()->day()->time();
            return $this;
        }

        $values = str_split($format);
        foreach ($values as $value) {
            $value = strtolower($value);
            if (!isset($this->formats[$value])) {
                continue;
            }
            $method = $this->formats[$value];
            $this->$method();
            if (in_array($value, ['c', 'x', 'l'])) {
                break;
            }
        }
        return $this;
    }

    /**
     * Sets the time formating option.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function time($enable = true)
    {
        return $this->option('time', $enable);
    }

    /**
     * Sets the year formating option.
     *
     * @param   bool    $enable
     * @return  self
     */
    public function year($enable = true)
    {
        return $this->option('year', $enable);
    }

    /**
     * Creates a parsed year value for use with decades and centuries.
     *
     * @param   DateTime    $date
     * @param   int         $length
     * @return  year
     */
    private function createYearValue(DateTime $date, $length)
    {
        $length = (Integer) $length;
        $year = $date->format('Y');
        $pointer = strlen($year) - $length;
        $value = (Integer) substr($year, 0, $pointer);
        return sprintf('%s%ss', $value, str_repeat('0', $length));

    }

    /**
     * Formats the century value.
     *
     * @param   DateTime    $date
     * @return  string
     */
    private function formatCentury(DateTime $date)
    {
        return $this->createYearValue($date, 2);
    }

    /**
     * Formats the date value.
     *
     * @param   DateTime    $date
     * @return  string
     */
    private function formatDate(DateTime $date)
    {
        $values = [
            $this->formatDayOfWeek($date),
            $this->formatMonth($date),
            $this->formatDay($date),
            $this->formatYear($date),
            $this->formatTime($date),
        ];
        foreach ($values as $index => $value) {
            if (empty($value)) {
                unset($values[$index]);
            }
        }
        return implode(' ', $values);
    }

    /**
     * Formats the day value.
     *
     * @param   DateTime    $date
     * @return  string|null
     */
    private function formatDay(DateTime $date)
    {
        if (false === $this->options['day']) {
            return;
        }
        $day = $date->format('j');
        return true === $this->options['year'] ? sprintf('%s,', $day) : $day;
    }

    /**
     * Formats the dayofweek value.
     *
     * @param   DateTime    $date
     * @return  string|null
     */
    private function formatDayOfWeek(DateTime $date)
    {
        if (false === $this->options['dayofweek']) {
            return;
        }
        return $date->format('l');
    }

    /**
     * Formats the decade value.
     *
     * @param   DateTime    $date
     * @param   bool        $long
     * @return  string
     */
    private function formatDecade(DateTime $date, $long = false)
    {
        if (false === $long) {
            $year = $date->format('y');
            return sprintf('\'%s0s', substr($year, 0, 1));
        }
        return $this->createYearValue($date, 1);
    }

    /**
     * Formats the month value.
     *
     * @param   DateTime    $date
     * @return  string|null
     */
    private function formatMonth(DateTime $date)
    {
        if (false === $this->options['month']) {
            return;
        }
        if (false === $this->options['day']) {
            return $date->format('F');
        }
        $num = (Integer) $date->format('n');
        return ($num >= 3 && $num <= 7) ? $date->format('F') : sprintf('%s.', $date->format('M'));
    }

    /**
     * Formats the time value.
     *
     * @param   DateTime    $date
     * @return  string|null
     */
    private function formatTime(DateTime $date)
    {
        if (false === $this->options['time']) {
            return;
        }
        $hour = (Integer) $date->format('g');
        $minutes = $date->format('i');
        $meridiem = sprintf('%s.', implode('.', str_split($date->format('a'))));
        if (12 === $hour && '00' === $minutes) {
            return 'a.m.' === $meridiem ? 'midnight' : 'noon';
        }
        $minutes = ('00' === $minutes) ? '' : sprintf(':%s', $minutes);
        return sprintf('%s%s %s', $hour, $minutes, $meridiem);
    }

    /**
     * Formats the year value.
     *
     * @param   DateTime    $date
     * @return  string|null
     */
    private function formatYear(DateTime $date)
    {
        if (false === $this->options['year']) {
            return;
        }
        return (String) (Integer) $date->format('Y');
    }

    /**
     * Handles date range formatting where start and end are on the same day.
     *
     * @param   DateTime    $start
     * @param   DateTime    $end
     * @return  string
     */
    private function handleSameDayRange(DateTime $start, DateTime $end)
    {
        $options = $this->options;
        $this->reset()->time();

        $startTime = $this->format($start);
        $endTime = $this->format($end);

        $this->options = $options;

        if (in_array($startTime, ['noon', 'midnight']) || in_array($endTime, ['noon', 'midnight'])) {
            return sprintf('%s-%s', $this->format($start), $endTime);
        }

        $startParts = explode(' ', $startTime);
        $endParts = explode(' ', $endTime);
        $range = ($startParts[1] === $endParts[1]) ? sprintf('%s-%s', $startParts[0], $endTime) : sprintf('%s-%s', $startTime, $endTime);

        $this->time(false);
        $value = sprintf('%s %s', $this->format($start), $range);
        $this->time();

        return $value;
    }

    /**
     * Sets a formatting option.
     *
     * @param   string  $key
     * @param   bool    $enable
     * @return  self
     */
    private function option($key, $enable)
    {
        $exclusive = ['century', 'decade', 'longdecade'];
        $enable = (Boolean) $enable;
        if (in_array($key, $exclusive) && true === $enable) {
            $this->reset();
        }
        if (!in_array($key, $exclusive) && true === $enable) {
            $this->options['century'] = false;
            $this->options['decade'] = false;
            $this->options['longdecade'] = false;
        }
        $this->options[$key] = $enable;
        return $this;
    }
}
