<?php
class My_DateTime extends DateTime
{
    const HISTORY_WEEK_NUM = 4;

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
            return 'even';
        }
        return 'odd';
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

    public static function getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount = self::HISTORY_WEEK_NUM)
    {
        $historyWeeks = array();
        $start = new DateTime($fromYear . 'W' . sprintf("%02d", $fromWeek));
        for ($i = $weeksCount; $i > 0; $i-- ) {
            $historyDate = clone $start;
            $historyDate->modify('-' . $i . ' week');
            $year = $historyDate->format('Y');
            $week = $historyDate->format('W');
            $historyWeeks[$week] = $year;
        }
        //$historyWeeks = array_reverse($historyWeeks, true);
        return  $historyWeeks;
    }
}