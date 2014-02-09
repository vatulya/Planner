<?php
class My_DateTime extends DateTime
{
    const HISTORY_WEEK_NUM = 4;

    static public function factory($date)
    {
        if ( ! $date instanceof My_DateTime) {
            $date = new My_DateTime($date);
        }
        return $date;
    }

    static public function isToday($date)
    {
        if ( ! $date instanceof My_DateTime) {
            $date = new My_DateTime($date);
        }
        $today = new My_DateTime();
        if ($today->format('Y-m-d') == $date->format('Y-m-d')) {
            return true;
        }
        return false;
    }

    static public  function TimeToDecimal($seconds)
    {
        // OLD logic :) just for fun
//        $minutes = floor($seconds / 60);       // 8000 / 60  = 133 minutes
//        $hours   = floor($minutes / 60);       // 133  / 60  = 2 hours
//        $seconds = (int)($seconds % $minutes); // 8000 % 133 = 20 seconds
//        $minutes = (int)($minutes % 60);       // 133  % 60  = 13 minutes
//        $decimalMinutes = $minutes / 60;       // 13   / 60  = 0.22
//        $decimalHours = $hours + $decimalMinutes; // 2 + 0.22 = 2.22 (8000 seconds = 2.22 hours
        // NEW logic
        $hours = $seconds / 3600; // 2.222222
        $hours = sprintf('%01.2f', $hours); // 2.22
        return $hours;
    }

    static public function diffInSeconds($start, $end)
    {
        $start = My_DateTime::factory($start);
        $end   = My_DateTime::factory($end);

        $start = $start->getTimestamp();
        $end   = $end->getTimestamp();

        $diff = $end - $start;
        return $diff;
    }

    static public function compare($a, $b)
    {
        $a = My_DateTime::factory($a)->getTimestamp();
        $b = My_DateTime::factory($b)->getTimestamp();
        $result = 0;
        switch (true) {
            case ($a > $b):
                $result = 1;
                break;

            case ($a < $b):
                $result = -1;
                break;

            default:
                $result = 0;
                break;
        }
        return $result;
    }

    static public function normalizeDate($dates)
    {
        if ( ! is_array($dates)) {
            $dates = explode(',', $dates);
        }
        $dates = array_filter($dates);
        $normalizedData = array();
        foreach ($dates as $key => $value) {
            $fail = false;
            try {
                $key = '';
                $value = new DateTime($value);
                $key = $value->format('Y-m-d');
                if (array_key_exists($key, $dates)) {
                    $fail = true;
                }

            } catch (Exception $e) {$fail = true;}
            if ($fail) {
                $key = '';
                $value = '';
            }
            $normalizedData[$key] = $value;
        }
        $normalizedData = array_filter($normalizedData);
        return $normalizedData;
    }

    static public function sortDates(array $dates)
    {
        $sort = function(DateTime $a, DateTime $b) {
            $aU = $a->format('U');
            $bU = $b->format('U');
            if ($aU < $bU) {
                return -1;
            }
            if ($aU > $bU) {
                return 1;
            }
            return 0;
        };
        usort($dates, $sort);
        return $dates;
    }

    static public function toStringDates(array $dates, $format = 'Y-m-d')
    {
        foreach ($dates as $key => $value) {
            $dates[$key] = $value->format($format);
        }
        return $dates;
    }

    static public function getWeekYear($timestamp = "")
    {
        if (empty($timestamp)) {
            $timestamp = time ();
        }
        try {
            $weekYear['week'] = strftime('%V', $timestamp);
            $weekYear['year'] = strftime('%G', $timestamp);
            $weekYear['day'] = strftime('%u', $timestamp);
            $weekYear['month'] = strftime('%m', $timestamp);
            $weekYear['monthDay'] = strftime('%e', $timestamp);
            if (empty( $weekYear['week']) || empty( $weekYear['year']) || empty($weekYear['day'])) {
                $weekYear['week'] = date('W',$timestamp);
                $weekYear['year'] = date('o',$timestamp);
                $weekYear['day'] = date('N',$timestamp);
            }
        } catch (Exception $e) {
            //TODO
        }
        return $weekYear;
    }

    static public function getWeekYearByDate($dateformat)
    {
        $timestamp = '';
        try {
            $date = My_DateTime::factory($dateformat);
            $date->getTimestamp();
        } catch (Exception $e) {
            //nothing will return current week year
        }
        return self::getWeekYear($timestamp);
    }

    static public function getTimestampByYearWeek($year, $week)
    {
        try {
            $date = new My_DateTime($year . 'W' . sprintf("%02d", $week));
            return $date->getTimestamp();
        } catch (Exception $e) {

        }
    }

    static public function getNextYearWeek($year, $week)
    {
        $date = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $date->modify('+1 week');
        $weekYear = self::getWeekYear($date->getTimestamp());
        return $weekYear;
    }

    static public function getTimestampNextWeekByYearWeek($year, $week)
    {
        try {
        $date = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $date->modify('+1 week');
        return $date->getTimestamp();
        } catch (Exception $e) {

        }
    }

    static public function getPrevYearWeek($year, $week)
    {
        $date = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $date->modify('-1 week');
        $weekYear = self::getWeekYear($date->getTimestamp());
        return $weekYear;
    }

    static public function getEvenWeek($weekNumber)
    {
        if ($weekNumber  % 2 > 0) {
            return 'odd';
        }
        return 'even';
    }

    public static function getWeekDays()
    {
        $weekDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        return $weekDays;
    }

    public static function getWeekHistoryDateInterval($year, $week)
    {
        $date = new DateTime($year . 'W' . sprintf("%02d", $week));
        $historyDateInterval['start'] =  $date->format('Y-m-d');
        $date->modify('+6 day');
        $historyDateInterval['end'] = $date->format('Y-m-d');
        return $historyDateInterval;
    }

    public static function getYearDateInterval($year)
    {
        $date = new DateTime($year."-01-01");
        $historyDateInterval['start'] =  $date->format('Y-m-d');
        $date->modify('+1 year');
        $date->modify('-1 day');
        $historyDateInterval['end'] = $date->format('Y-m-d');
        return $historyDateInterval;
    }

    public static function getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount = self::HISTORY_WEEK_NUM)
    {
        $historyWeeks = array();
        $start = new DateTime($fromYear . 'W' . sprintf("%02d", $fromWeek));
        for ($i = $weeksCount; $i > 0; $i-- ) {
            $historyDate = clone $start;
            $historyDate->modify('-' . $i . ' week');
            $year = strftime('%G', $historyDate->getTimestamp());
            $week = strftime('%V', $historyDate->getTimestamp());
            //For start on win envs
            if (empty($year) || empty($week)) {
                $week = date('W',$historyDate->getTimestamp());
                $year = date('o',$historyDate->getTimestamp());
            }
            $historyWeeks[$week] = $year;
        }
        //$historyWeeks = array_reverse($historyWeeks, true);
        return  $historyWeeks;
    }

    public static function splitTimeString($timeString)
    {
        try {
        $startTime = new DateTime($timeString);
        $startHourValue = $startTime->format('H');
        $startMinValue  = $startTime->format('i');
        return array('hour' => $startHourValue, 'min' => $startMinValue);
        } catch (Exception $e) {
            return array('hour' => '', 'min' => '');
        }
    }

    public static function formatTime($time)
    {
        try {
            $date = date_create($time);
        } catch (Exception $e) {
            return $time;
        }
        return date_format($date, 'H:i');
    }

    public static function isFutureDate($date)
    {
        $date = My_DateTime::factory($date);
        $now  = My_DateTime::factory('now');
        $compare = My_DateTime::compare($now, $date);
        if ($compare > 0) {
            return true;
        }
        return false;
    }

    public static function getWeekDateStart($year, $week)
    {

    }

    public static function getDateIntervalByWeekYear($year, $week)
    {
        $date = new DateTime($year . 'W' . sprintf("%02d", $week));
        $dateInterval['start'] =  $date->format('Y-m-d');
        $date->modify('+1 week');
        $dateInterval['end'] =  $date->format('Y-m-d');
        return $dateInterval;
    }

    public static function getArrayOfDatesInterval($countOfInterval, $week, $year, $date = '')
    {
        $datesInterval = array();
        if (empty($timestamp)) {
            $timestamp = time ();
        }

        try {
            $weekInterval = self::getWeekHistoryDateInterval($year, $week);
            $date = new DateTime($weekInterval['start']);
            for ($i = 0; $i < $countOfInterval; $i++) {
                $isWeekend = false;
                $weekDay = strftime('%u', $date->getTimestamp());
                if ($weekDay == 6 || $weekDay == 7) {
                    $isWeekend = true;
                }
                $datesInterval[]= array(
                    'day' => strftime('%A', $date->getTimestamp()),
                    'date' => $date->format('Y-m-d'),
                    'isWeekend' => $isWeekend
                );
                $date->modify('+1 day');
            }
        } catch (Exception $e) {
            //TODO
        }
        return $datesInterval;
    }
}