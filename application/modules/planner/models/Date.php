<?php

class Planner_Model_Date extends DateTime
{
    public function getIsoMonday($year, $week) {
        # check input
        //TODO дописать проверку на наличие такой недели в таком году
        $year = min ($year, 2038); $year = max ($year, 1970);
        $week = min ($week, 53); $week = max ($week, 1);
        # make a guess
        $monday = mktime (1,1,1,1,7*$week,$year);
        # count down to week
        while (strftime('%V', $monday) != $week)
            $monday -= 60*60*24*7;
        # count down to monday
        while (strftime('%u', $monday) != 1)
            $monday -= 60*60*24;
        # got it
        return $monday;
    }

    public function getWeekYear($timestamp = "")
    {
        if (empty($timestamp)) {
            $timestamp = time ();
        } else {
           // $timestamp = mktime($date);
        }
        $weekYear['week'] = strftime('%V', $timestamp);
        $weekYear['year'] = strftime('%G', $timestamp);
        return $weekYear;
    }

    public static function getWeekDays()
    {
        $weekDays = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        return $weekDays;
    }

}
