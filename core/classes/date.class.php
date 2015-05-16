<?php

/**
 * 日期处理类
 *
 * 本类库实例化Date对象部分从 PHPRPC 中的 `PHPRPC_Date` 类库移植而来， [http://www.phprpc.org/](http://www.phprpc.org/)

 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @copyright  www.phprpc.org
 * @see        http://www.phprpc.org/
 * @license    http://www.myqee.com/license.html
 */
class Core_Date
{

    // Second amounts for various time increments
    const YEAR   = 31556926;
    const MONTH  = 2629744;
    const WEEK   = 604800;
    const DAY    = 86400;
    const HOUR   = 3600;
    const MINUTE = 60;

    // Available formats for Date::months()
    const MONTHS_LONG = '%B';
    const MONTHS_SHORT = '%b';

    /**
     * Default timestamp format for formatted_time
     * @var  string
     */
    public static $timestamp_format = 'Y-m-d H:i:s';

    /**
     * Timezone for formatted_time
     * @link http://uk2.php.net/manual/en/timezones.php
     * @var  string
     */
    public static $timezone;

    /**
     * Returns the offset (in seconds) between two time zones. Use this to
     * display dates to users in different time zones.
     *
     *     $seconds = Date::offset('America/Chicago', 'GMT');
     *
     * [!!] A list of time zones that PHP supports can be found at
     * <http://php.net/timezones>.
     *
     * @param   string   timezone that to find the offset of
     * @param   string   timezone used as the baseline
     * @param   mixed    UNIX timestamp or date string
     * @return  integer
     */
    public static function offset($remote, $local = null, $now = null)
    {
        if ($local === null)
        {
            // Use the default timezone
            $local = date_default_timezone_get();
        }

        if (is_int($now))
        {
            // Convert the timestamp into a string
            $now = date(DateTime::RFC2822, $now);
        }

        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local = new DateTimeZone($local);

        // Create date objects from timezones
        $time_remote = new DateTime($now, $zone_remote);
        $time_local = new DateTime($now, $zone_local);

        // Find the offset
        $offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);

        return $offset;
    }

    /**
     * Number of seconds in a minute, incrementing by a step. Typically used as
     * a shortcut for generating a list that can used in a form.
     *
     *     $seconds = Date::seconds(); // 01, 02, 03, ..., 58, 59, 60
     *
     * @param   integer  amount to increment each step by, 1 to 30
     * @param   integer  start value
     * @param   integer  end value
     * @return  array    A mirrored (foo => foo) array from 1-60.
     */
    public static function seconds($step = 1, $start = 0, $end = 60)
    {
        // Always integer
        $step = (int)$step;

        $seconds = array();

        for($i = $start; $i < $end; $i += $step)
        {
            $seconds[$i] = sprintf('%02d', $i);
        }

        return $seconds;
    }

    /**
     * Number of minutes in an hour, incrementing by a step. Typically used as
     * a shortcut for generating a list that can be used in a form.
     *
     *     $minutes = Date::minutes(); // 05, 10, 15, ..., 50, 55, 60
     *
     * @uses    Date::seconds
     * @param   integer  amount to increment each step by, 1 to 30
     * @return  array    A mirrored (foo => foo) array from 1-60.
     */
    public static function minutes($step = 5)
    {
        // Because there are the same number of minutes as seconds in this set,
        // we choose to re-use seconds(), rather than creating an entirely new
        // function. Shhhh, it's cheating! ;) There are several more of these
        // in the following methods.
        return Date::seconds($step);
    }

    /**
     * Number of hours in a day. Typically used as a shortcut for generating a
     * list that can be used in a form.
     *
     *     $hours = Date::hours(); // 01, 02, 03, ..., 10, 11, 12
     *
     * @param   integer  amount to increment each step by
     * @param   boolean  use 24-hour time
     * @param   integer  the hour to start at
     * @return  array    A mirrored (foo => foo) array from start-12 or start-23.
     */
    public static function hours($step = 1, $long = false, $start = null)
    {
        // Default values
        $step = (int)$step;
        $long = (bool)$long;
        $hours = array();

        // Set the default start if none was specified.
        if ($start === null)
        {
            $start = ($long === false) ? 1 : 0;
        }

        $hours = array();

        // 24-hour time has 24 hours, instead of 12
        $size = ($long === TRUE) ? 23 : 12;

        for( $i = $start; $i <= $size; $i += $step )
        {
            $hours[$i] = (string)$i;
        }

        return $hours;
    }

    /**
     * Returns AM or PM, based on a given hour (in 24 hour format).
     *
     *     $type = Date::ampm(12); // PM
     *     $type = Date::ampm(1);  // AM
     *
     * @param   integer  number of the hour
     * @return  string
     */
    public static function ampm($hour)
    {
        // Always integer
        $hour = (int)$hour;

        return ($hour > 11) ? 'PM' : 'AM';
    }

    /**
     * Adjusts a non-24-hour number into a 24-hour number.
     *
     *     $hour = Date::adjust(3, 'pm'); // 15
     *
     * @param   integer  hour to adjust
     * @param   string   AM or PM
     * @return  string
     */
    public static function adjust($hour, $ampm)
    {
        $hour = (int)$hour;
        $ampm = strtolower($ampm);

        switch ($ampm)
        {
            case 'am' :
                if ($hour == 12)
                {
                    $hour = 0;
                }
                break;
            case 'pm' :
                if ($hour < 12)
                {
                    $hour += 12;
                }
                break;
        }

        return sprintf('%02d', $hour);
    }

    /**
     * Number of days in a given month and year. Typically used as a shortcut
     * for generating a list that can be used in a form.
     *
     *     Date::days(4, 2010); // 1, 2, 3, ..., 28, 29, 30
     *
     * @param   integer  number of month
     * @param   integer  number of year to check month, defaults to the current year
     * @return  array    A mirrored (foo => foo) array of the days.
     */
    public static function days($month, $year = false)
    {
        static $months = array();

        if ($year === false)
        {
            // Use the current year by default
            $year = date('Y');
        }

        // Always integers
        $month = (int)$month;
        $year  = (int)$year;

        // We use caching for months, because time functions are used
        if (empty($months[$year][$month]))
        {
            $months[$year][$month] = array();

            // Use date to find the number of days in the given month
            $total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

            for($i = 1; $i < $total; $i ++)
            {
                $months[$year][$month][$i] = (string)$i;
            }
        }

        return $months[$year][$month];
    }

    /**
     * Number of months in a year. Typically used as a shortcut for generating
     * a list that can be used in a form.
     *
     * By default a mirrored array of $month_number => $month_number is returned
     *
     *     Date::months();
     *     // aray(1 => 1, 2 => 2, 3 => 3, ..., 12 => 12)
     *
     * But you can customise this by passing in either Date::MONTHS_LONG
     *
     *     Date::months(Date::MONTHS_LONG);
     *     // array(1 => 'January', 2 => 'February', ..., 12 => 'December')
     *
     * Or Date::MONTHS_SHORT
     *
     *     Date::months(Date::MONTHS_SHORT);
     *     // array(1 => 'Jan', 2 => 'Feb', ..., 12 => 'Dec')
     *
     * @uses    Date::hours
     * @param   string The format to use for months
     * @return  array  An array of months based on the specified format
     */
    public static function months($format = null)
    {
        $months = array();

        if ( $format === DATE::MONTHS_LONG or $format === DATE::MONTHS_SHORT )
        {
            for( $i = 1; $i <= 12; ++ $i )
            {
                $months[$i] = strftime($format, mktime(0, 0, 0, $i, 1));
            }
        }
        else
        {
            $months = Date::hours();
        }

        return $months;
    }

    /**
     * Returns an array of years between a starting and ending year. By default,
     * the the current year - 5 and current year + 5 will be used. Typically used
     * as a shortcut for generating a list that can be used in a form.
     *
     *     $years = Date::years(2000, 2010); // 2000, 2001, ..., 2009, 2010
     *
     * @param   integer  starting year (default is current year - 5)
     * @param   integer  ending year (default is current year + 5)
     * @return  array
     */
    public static function years($start = false, $end = false)
    {
        // Default values
        $start = ($start === false) ? (date('Y') - 5) : (int)$start;
        $end = ($end === false) ? (date('Y') + 5) : (int)$end;

        $years = array();

        for($i = $start; $i <= $end; $i ++)
        {
            $years[$i] = (string)$i;
        }

        return $years;
    }

    /**
     * Returns time difference between two timestamps, in human readable format.
     * If the second timestamp is not given, the current time will be used.
     * Also consider using [Date::fuzzy_span] when displaying a span.
     *
     *     $span = Date::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     *     $span = Date::span(60, 182, 'minutes'); // 2
     *
     * @param   integer  timestamp to find the span of
     * @param   integer  timestamp to use as the baseline
     * @param   string   formatting string
     * @return  string   when only a single output is requested
     * @return  array    associative list of all outputs requested
     */
    public static function span($remote, $local = null, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Normalize output
        $output = trim(strtolower((string)$output));

        if (!$output)
        {
            // Invalid output
            return false;
        }

        // Array with the output formats
        $output = preg_split('/[^a-z]+/', $output);

        // Convert the list of outputs to an associative array
        $output = array_combine($output, array_fill(0, count($output), 0));

        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);

        if ($local === null)
        {
            // Calculate the span from the current time
            $local = time();
        }

        // Calculate timespan (seconds)
        $timespan = abs($remote - $local);

        if (isset($output['years']))
        {
            $timespan -= Date::YEAR * ($output['years'] = (int)floor($timespan / Date::YEAR));
        }

        if (isset($output['months']))
        {
            $timespan -= Date::MONTH * ($output['months'] = (int)floor($timespan / Date::MONTH));
        }

        if (isset($output['weeks']))
        {
            $timespan -= Date::WEEK * ($output['weeks'] = (int)floor($timespan / Date::WEEK));
        }

        if (isset($output['days']))
        {
            $timespan -= Date::DAY * ($output['days'] = (int)floor($timespan / Date::DAY));
        }

        if (isset($output['hours']))
        {
            $timespan -= Date::HOUR * ($output['hours'] = (int)floor($timespan / Date::HOUR));
        }

        if (isset($output['minutes']))
        {
            $timespan -= Date::MINUTE * ($output['minutes'] = (int)floor($timespan / Date::MINUTE));
        }

        // Seconds ago, 1
        if (isset($output['seconds']))
        {
            $output['seconds'] = $timespan;
        }

        if (count($output) === 1)
        {
            // Only a single output was requested, return it
            return array_pop($output);
        }

        // Return array
        return $output;
    }

    /**
     * Returns the difference between a time and now in a "fuzzy" way.
     * Displaying a fuzzy time instead of a date is usually faster to read and understand.
     *
     *     $span = Date::fuzzy_span(time() - 10); // "moments ago"
     *     $span = Date::fuzzy_span(time() + 20); // "in moments"
     *
     * A second parameter is available to manually set the "local" timestamp,
     * however this parameter shouldn't be needed in normal usage and is only
     * included for unit tests
     *
     * @param   integer  "remote" timestamp
     * @param   integer  "local" timestamp, defaults to time()
     * @return  string
     */
    public static function fuzzy_span($timestamp, $local_timestamp = null)
    {
        $local_timestamp = ($local_timestamp === null) ? time() : (int)$local_timestamp;

        // Determine the difference in seconds
        $offset = abs($local_timestamp - $timestamp);

        if ($offset <= Date::MINUTE)
        {
            $span = 'moments';
        }
        elseif ($offset < (Date::MINUTE * 20))
        {
            $span = 'a few minutes';
        }
        elseif ($offset < Date::HOUR )
        {
            $span = 'less than an hour';
        }
        elseif ($offset < (Date::HOUR * 4))
        {
            $span = 'a couple of hours';
        }
        elseif ($offset < Date::DAY )
        {
            $span = 'less than a day';
        }
        elseif ($offset < (Date::DAY * 2))
        {
            $span = 'about a day';
        }
        elseif ($offset < (Date::DAY * 4))
        {
            $span = 'a couple of days';
        }
        elseif ($offset < Date::WEEK )
        {
            $span = 'less than a week';
        }
        elseif ($offset < (Date::WEEK * 2))
        {
            $span = 'about a week';
        }
        elseif ($offset < Date::MONTH )
        {
            $span = 'less than a month';
        }
        elseif ($offset < (Date::MONTH * 2))
        {
            $span = 'about a month';
        }
        elseif ($offset < (Date::MONTH * 4))
        {
            $span = 'a couple of months';
        }
        elseif ($offset < Date::YEAR )
        {
            $span = 'less than a year';
        }
        elseif ($offset < (Date::YEAR * 2))
        {
            $span = 'about a year';
        }
        elseif ($offset < (Date::YEAR * 4))
        {
            $span = 'a couple of years';
        }
        elseif ($offset < (Date::YEAR * 8))
        {
            $span = 'a few years';
        }
        elseif ($offset < (Date::YEAR * 12))
        {
            $span = 'about a decade';
        }
        elseif ($offset < (Date::YEAR * 24))
        {
            $span = 'a couple of decades';
        }
        elseif ($offset < (Date::YEAR * 64))
        {
            $span = 'several decades';
        }
        else
        {
            $span = 'a long time';
        }

        if ($timestamp <= $local_timestamp)
        {
            // This is in the past
            return $span . ' ago';
        }
        else
        {
            // This in the future
            return 'in ' . $span;
        }
    }

    /**
     * Converts a UNIX timestamp to DOS format. There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::dos2unix}.
     *
     *     $dos = Date::unix2dos($unix);
     *
     * @param   integer  UNIX timestamp
     * @return  integer
     */
    public static function unix2dos($timestamp = false)
    {
        $timestamp = ($timestamp === false) ? getdate() : getdate($timestamp);

        if ($timestamp['year'] < 1980)
        {
            return (1 << 21 | 1 << 16);
        }

        $timestamp['year'] -= 1980;

        // What voodoo is this? I have no idea... Geert can explain it though,
        // and that's good enough for me.
        return ($timestamp['year'] << 25 | $timestamp['mon'] << 21 | $timestamp['mday'] << 16 | $timestamp['hours'] << 11 | $timestamp['minutes'] << 5 | $timestamp['seconds'] >> 1);
    }

    /**
     * Converts a DOS timestamp to UNIX format.There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::unix2dos}.
     *
     *     $unix = Date::dos2unix($dos);
     *
     * @param   integer  DOS timestamp
     * @return  integer
     */
    public static function dos2unix($timestamp = false)
    {
        $sec  = 2 * ($timestamp & 0x1f);
        $min  = ($timestamp >> 5) & 0x3f;
        $hrs  = ($timestamp >> 11) & 0x1f;
        $day  = ($timestamp >> 16) & 0x1f;
        $mon  = ($timestamp >> 21) & 0x0f;
        $year = ($timestamp >> 25) & 0x7f;

        return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
    }

    /**
     * Returns a date/time string with the specified timestamp format
     *
     *     $time = Date::formatted_time('5 minutes ago');
     *
     * @see     http://php.net/manual/en/datetime.construct.php
     * @param   string  datetime_str     datetime string
     * @param   string  timestamp_format timestamp format
     * @return  string
     */
    public static function formatted_time($datetime_str = 'now', $timestamp_format = null, $timezone = null)
    {
        $timestamp_format = ($timestamp_format == null) ? Date::$timestamp_format : $timestamp_format;
        $timezone = ($timezone === null) ? Date::$timezone : $timezone;

        $time = new DateTime($datetime_str, new DateTimeZone($timezone ? $timezone : date_default_timezone_get()));

        return $time->format($timestamp_format);
    }

    /**
     * 是否闰年
     *
     *      Data::isLeapYear(2000);     // true
     *
     * @param $year
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return (($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false;
    }

    /**
     * 返回某个历法中某年中某月的天数
     *
     *     Data::daysInMonth(2003, 8);      // 31
     *
     * @see http://php.net/cal_days_in_month
     * @use   cal_days_in_month
     * @param $year
     * @param $month
     * @return bool|int
     */
    public static function daysInMonth($year, $month)
    {
        if (($month < 1) || ($month > 12))
        {
            return false;
        }
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * 验证一个格里高里日期
     *
     *      Data::isValidDate(2000, 12, 31);        // true
     *      Data::isValidDate(2001, 2, 29);         // false
     *
     * @param $year
     * @param $month
     * @param $day
     * @return bool
     */
    public static function isValidDate($year, $month, $day)
    {
        if (($year >= 1) && ($year <= 9999))
        {
            return checkdate($month, $day, $year);
        }
        return false;
    }

    /**
     * 是否一有效的时间
     *
     *      Data::isValidTime(23, 23, 30);      //true
     *      Data::isValidTime(25, 23, 30);      //false
     *
     * @param $hour
     * @param $minute
     * @param $second
     * @return bool
     */
    public static function isValidTime($hour, $minute, $second)
    {
        return !(($hour < 0) || ($hour > 23) || ($minute < 0) || ($minute > 59) || ($second < 0) || ($second > 59));
    }

    // public static methods

    public static function now()
    {
        $date = new Date();
        return $date;
    }

    public static function today()
    {
        $date = Date::now();
        $date->hour   = 0;
        $date->minute = 0;
        $date->second = 0;
        return $date;
    }

    public static function parse($dt)
    {
        if (is_a($dt, 'Date'))
        {
            return $dt;
        }
        if (is_int($dt))
        {
            return new Date($dt);
        }

        $shortFormat = '(\d|\d{2}|\d{3}|\d{4})-([1-9]|0[1-9]|1[012])-([1-9]|0[1-9]|[12]\d|3[01])';
        if (preg_match("/^$shortFormat$/", $dt, $match))
        {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (Date::isValidDate($year, $month, $day))
            {
                $date = new Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                return $date;
            }
            else
            {
                return false;
            }
        }
        $longFormat = $shortFormat . ' (\d|0\d|1\d|2[0-3]):(\d|[0-5]\d):(\d|[0-5]\d)';
        if (preg_match("/^$longFormat$/", $dt, $match))
        {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (Date::isValidDate($year, $month, $day))
            {
                $date = new Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                $date->hour   = intval($match[4]);
                $date->minute = intval($match[5]);
                $date->second = intval($match[6]);
                return $date;
            }
            else
            {
                return false;
            }
        }
        $fullFormat = $longFormat . '\.(\d|\d{2}|\d{3})';
        if (preg_match("/^$fullFormat$/", $dt, $match))
        {
            $year   = intval($match[1]);
            $month  = intval($match[2]);
            $day    = intval($match[3]);
            if (Date::isValidDate($year, $month, $day))
            {
                $date = new Date(false);
                $date->year  = $year;
                $date->month = $month;
                $date->day   = $day;
                $date->hour        = intval($match[4]);
                $date->minute      = intval($match[5]);
                $date->second      = intval($match[6]);
                $date->millisecond = intval($match[7]);
                return $date;
            }
            else
            {
                return false;
            }
        }
        return false;
    }



    protected $year   = 1;
    protected $month  = 1;
    protected $day    = 1;
    protected $hour   = 0;
    protected $minute = 0;
    protected $second = 0;
    protected $millisecond = 0;

    /**
     * 初始化对象方法
     *
     *      // 传入一个字符串
     *      $date = new Data('2014-01-20 11:20:12');
     *
     *      // 传入一个时间戳
     *      $date = new Data(1389670996);
     *
     *      // 当前时间
     *      $date = new Data();
     *
     * @return $this
     */
    public function __construct()
    {
        $num = func_num_args();

        $time = false;
        if ($num == 0)
        {
            $time = getdate();
        }
        else if ($num == 1)
        {
            $arg = func_get_arg(0);
            if (is_int($arg))
            {
                $time = getdate($arg);
            }
            elseif (is_string($arg))
            {
                $time = getdate(strtotime($arg));
            }
        }

        if (is_array($time))
        {
            $this->year   = $time['year'];
            $this->month  = $time['mon'];
            $this->day    = $time['mday'];
            $this->hour   = $time['hours'];
            $this->minute = $time['minutes'];
            $this->second = $time['seconds'];
        }
    }

    /**
     * 返回一个Date的实例化对象
     *
     *      // 传入一个字符串
     *      $date = Data::factory('2014-01-20 11:20:12');
     *
     *      // 传入一个时间戳
     *      $date = Data::factory(1389670996);
     *
     *      // 当前时间
     *      $date = Data::factory();
     *
     * @return Date
     */
    public static function factory()
    {
        $num = func_num_args();

        if ($num == 1)
        {
            return new Date(func_get_arg(0));
        }
        else
        {
            return new Date();
        }
    }

    // public instance methods

    /**
     * 增加毫秒
     *
     * @param $milliseconds
     * @return $this
     */
    public function addMilliseconds($milliseconds)
    {
        if (!is_int($milliseconds)) return $this;
        if ($milliseconds == 0) return $this;
        $millisecond = $this->millisecond + $milliseconds;
        $milliseconds = $millisecond % 1000;
        if ($milliseconds < 0)
        {
            $milliseconds += 1000;
        }
        $seconds = (int)(($millisecond - $milliseconds) / 1000);
        if ($this->addSeconds($seconds))
        {
            $this->millisecond = (int)$milliseconds;
        }

        return $this;
    }

    /**
     * 增加秒
     *
     * @param $seconds
     * @return $this
     */
    public function addSeconds($seconds)
    {
        if (!is_int($seconds)) return $this;
        if ($seconds == 0) return $this;
        $second = $this->second + $seconds;
        $seconds = $second % 60;
        if ($seconds < 0)
        {
            $seconds += 60;
        }
        $minutes = (int)(($second - $seconds) / 60);
        if ($this->addMinutes($minutes))
        {
            $this->second = (int)$seconds;
        }

        return $this;
    }

    /**
     * 增加分钟
     *
     * @param $minutes
     * @return $this
     */
    public function addMinutes($minutes)
    {
        if (!is_int($minutes)) return $this;
        if ($minutes == 0) return $this;
        $minute = $this->minute + $minutes;
        $minutes = $minute % 60;
        if ($minutes < 0) {
            $minutes += 60;
        }
        $hours = (int)(($minute - $minutes) / 60);
        if ($this->addHours($hours))
        {
            $this->minute = (int)$minutes;
        }

        return $this;
    }

    /**
     * 增加小时
     *
     * @param $hours
     * @return $this
     */
    public function addHours($hours)
    {
        if (!is_int($hours)) return $this;
        if ($hours == 0) return $this;
        $hour = $this->hour + $hours;
        $hours = $hour % 24;
        if ($hours < 0)
        {
            $hours += 24;
        }
        $days = (int)(($hour - $hours) / 24);
        if ($this->addDays($days))
        {
            $this->hour = (int)$hours;
        }

        return $this;
    }

    /**
     * 增加天
     *
     *      Data::factory()->addDays(3);
     *
     * @param $days
     * @return $this
     */
    public function addDays($days)
    {
        if (!is_int($days)) return $this;
        $year = $this->year;
        if ($days == 0) return $this;
        if ($days >= 146097 || $days <= -146097)
        {
            $remainder = $days % 146097;
            if ($remainder < 0)
            {
                $remainder += 146097;
            }
            $years = 400 * (int)(($days - $remainder) / 146097);
            $year += $years;
            if ($year < 1 || $year > 9999) return $this;
            $days = $remainder;
        }

        if ($days >= 36524 || $days <= -36524)
        {
            $remainder = $days % 36524;
            if ($remainder < 0)
            {
                $remainder += 36524;
            }
            $years = 100 * (int)(($days - $remainder) / 36524);
            $year += $years;
            if ($year < 1 || $year > 9999) return $this;
            $days = $remainder;
        }

        if ($days >= 1461 || $days <= -1461)
        {
            $remainder = $days % 1461;
            if ($remainder < 0)
            {
                $remainder += 1461;
            }
            $years = 4 * (int)(($days - $remainder) / 1461);
            $year += $years;
            if ($year < 1 || $year > 9999) return $this;
            $days = $remainder;
        }

        $month = $this->month;
        while ($days >= 365)
        {
            if ($year >= 9999) return $this;
            if ($month <= 2)
            {
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false)
                {
                    $days -= 366;
                }
                else
                {
                    $days -= 365;
                }
                $year++;
            }
            else
            {
                $year++;
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false)
                {
                    $days -= 366;
                }
                else
                {
                    $days -= 365;
                }
            }
        }

        while ($days < 0)
        {
            if ($year <= 1) return $this;
            if ($month <= 2)
            {
                $year--;
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false)
                {
                    $days += 366;
                }
                else
                {
                    $days += 365;
                }
            }
            else
            {
                if ((($year % 4) == 0) ? (($year % 100) == 0) ? (($year % 400) == 0) : true : false)
                {
                    $days += 366;
                }
                else
                {
                    $days += 365;
                }
                $year--;
            }
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = $this->day;
        while ($day + $days > $daysInMonth)
        {
            $days -= $daysInMonth - $day + 1;
            $month++;
            if ($month > 12)
            {
                if ($year >= 9999)return $this;
                $year++;
                $month = 1;
            }
            $day = 1;
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }

        $day        += $days;
        $this->year  = $year;
        $this->month = $month;
        $this->day   = $day;

        return $this;
    }

    /**
     * 增加月
     *
     * @param $months
     * @return $this
     */
    public function addMonths($months)
    {
        if (!is_int($months)) return $this;
        if ($months == 0) return $this;
        $month = $this->month + $months;
        $months = ($month - 1) % 12 + 1;
        if ($months < 1)
        {
            $months += 12;
        }
        $years = (int)(($month - $months) / 12);
        if ($this->addYears($years))
        {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $months, $this->year);
            if ($this->day > $daysInMonth)
            {
                $months++;
                $this->day -= $daysInMonth;
            }
            $this->month = (int)$months;
        }

        return $this;
    }

    /**
     * 增加年
     *
     * @param $years
     * @return $this
     */
    public function addYears($years)
    {
        if (!is_int($years)) return $this;
        if ($years == 0) return $this;
        $year = $this->year + $years;
        if ($year < 1 || $year > 9999) return $this;
        $this->year = $year;

        return $this;
    }

    /**
     * 对比另外一个日期是否在当前日期之后
     *
     *      $data1 = Date::factory('2014-01-05');
     *      $data2 = Date::factory('2014-01-06');
     *      var_dump($data1->after($data2));
     *      // bool(true)
     *
     * @param $when
     * @return bool
     */
    public function after($when)
    {
        if (!is_a($when, 'Date'))
        {
            $when = Date::parse($when);
        }

        if ($this->year < $when->year) return false;
        if ($this->year > $when->year) return true;
        if ($this->month < $when->month) return false;
        if ($this->month > $when->month) return true;
        if ($this->day < $when->day) return false;
        if ($this->day > $when->day) return true;
        if ($this->hour < $when->hour) return false;
        if ($this->hour > $when->hour) return true;
        if ($this->minute < $when->minute) return false;
        if ($this->minute > $when->minute) return true;
        if ($this->second < $when->second) return false;
        if ($this->second > $when->second) return true;
        if ($this->millisecond < $when->millisecond) return false;
        if ($this->millisecond > $when->millisecond) return true;

        return false;
    }

    /**
     * 对比另外一个日期是否在当前日期之前
     *
     *      $data1 = Date::factory('2014-01-05');
     *      $data2 = Date::factory('2014-01-06');
     *      var_dump($data1->after($data2));
     *      // bool(false)
     *
     * @param $when
     * @return bool
     */
    public function before($when)
    {
        return !$this->after($when);
    }

    /**
     * 对比另外一个日期对象是否和当前时间相同
     *
     *
     *      $data1 = Date::factory('2014-01-05');
     *      $data2 = Date::factory('2014-01-05');
     *      $data3 = Date::factory('2014-01-05 10:11:11');
     *      var_dump($data1->equals($data2));
     *      // bool(true)
     *
     *      var_dump($data1->equals($data3));
     *      // bool(false)
     *
     * @param $when
     * @return bool
     */
    public function equals($when)
    {
        if (!is_a($when, 'Date'))
        {
            $when = new Date($when);
        }
        return (($this->year == $when->year) &&
            ($this->month == $when->month) &&
            ($this->day == $when->day) &&
            ($this->hour == $when->hour) &&
            ($this->minute == $when->minute) &&
            ($this->second == $when->second) &&
            ($this->millisecond == $when->millisecond));
    }

    /**
     * 设置时间
     *
     *      $date = Data::factory()->set(2014, 1, 30, 23, 12, 59, 113);
     *      echo $date->toString();
     *      // 2014-01-30 23:12:59.113
     *
     *      $date = Data::factory()->set(2014, 1, 30, 23);
     *      echo $date->toString();
     *      // 2014-01-30 23:00:00
     *
     * @return $this
     * @throws Exception
     */
    public function set()
    {
        $num = func_num_args();
        $args = func_get_args();
        if ($num >= 3)
        {
            if (!Date::isValidDate($args[0], $args[1], $args[2]))
            {
                throw new Exception('error date: '.$args[0] .'-'. $args[1] .'-'. $args[2]);
            }
            $this->year  = (int)$args[0];
            $this->month = (int)$args[1];
            $this->day   = (int)$args[2];
            if ($num == 3)
            {
                return $this;
            }
        }
        if ($num >= 6)
        {
            if (!Date::isValidTime($args[3], $args[4], $args[5]))
            {
                throw new Exception('error time: '.$args[3] .':'. $args[4] .':'. $args[5]);
            }
            $this->hour   = (int)$args[3];
            $this->minute = (int)$args[4];
            $this->second = (int)$args[5];
            if ($num == 6)
            {
                return $this;
            }
        }
        if (($num == 7) && ($args[6] >= 0 && $args[6] <= 999))
        {
            $this->millisecond = (int)$args[6];
            return $this;
        }

        return $this;
    }

    /**
     * 返回当前时间戳
     *
     * @return int
     */
    public function time()
    {
        return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    }

    /**
     * 输出字符串
     *
     *     echo Date::factory('2014-02-20')->toString();
     *     // 2014-02-20 00:00:00.000
     *
     * @return string
     */
    public function toString()
    {
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d.%03d',
            $this->year, $this->month, $this->day,
            $this->hour, $this->minute, $this->second,
            $this->millisecond);
    }

    /**
     * 输出字符串
     *
     *     echo (string)Date::factory('2014-02-20');
     *     // 2014-02-20 00:00:00.000
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * 返回当前时期是这一周的第几天
     *
     * 可以静态调用
     *
     *      echo Date::dayOfWeek(2014, 2, 20);      // 4
     *
     *      等同于
     *      $date = new Date('2014-02-20');
     *      echo $date->dayOfWeek();     //获取当前对象的年月日参数
     *
     * @param null $year
     * @param null $month
     * @param null $day
     * @return int
     */
    public function dayOfWeek($year = null, $month = null, $day = null)
    {
        $num = func_num_args();
        if ($num == 3)
        {
            $args = func_get_args();
            $y = $args[0];
            $m = $args[1];
            $d = $args[2];
        }
        else
        {
            $y = $this->year;
            $m = $this->month;
            $d = $this->day;
        }
        $d += $m < 3 ? $y-- : $y - 2;
        return ((int)(23 * $m / 9) + $d + 4 + (int)($y / 4) - (int)($y / 100) + (int)($y / 400)) % 7;
    }

    /**
     * 返回当前日期是这一年中的第几天
     *
     * 可以静态调用
     *
     *      echo Date::dayOfYear(2014, 2, 20);      // 51
     *
     *      等同于
     *      $date = new Date('2014-02-20');
     *      echo $date->dayOfYear();     //获取当前对象的年月日参数
     *
     * @param null $year
     * @param null $month
     * @param null $day
     * @return mixed
     */
    public function dayOfYear($year = null, $month = null, $day = null)
    {
        static $daysToMonth365 = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365);
        static $daysToMonth366 = array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366);
        $num = func_num_args();
        if ($num == 3)
        {
            $args = func_get_args();
            $y = $args[0];
            $m = $args[1];
            $d = $args[2];
        }
        else
        {
            $y = $this->year;
            $m = $this->month;
            $d = $this->day;
        }
        $days = Date::isLeapYear($y) ? $daysToMonth365 : $daysToMonth366;

        return $days[$m - 1] + $d;
    }
}
