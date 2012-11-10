<?php
class My_DateTime extends DateTime
{

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

}