<?php

class Application_Model_WorkIntervals extends Application_Model_Abstract
{

    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDbWork  = new Application_Model_Db_Intervals_Work();
        $this->_modelDbPause = new Application_Model_Db_Intervals_Pause();
    }

    public function getWorkIntervals()
    {
        $planning = new Application_Model_Planning();
        $workIntervals = $this->_modelDbWork->getWorkIntervals();
        foreach ($workIntervals as &$workInterval) {
            if (!empty($workInterval['time_start']) && !empty($workInterval['time_end'])) {
                $workInterval    = array_merge($workInterval, $planning->_splitStartEndTimeString($workInterval['time_start'], $workInterval['time_end']));
    //            if (!empty($day['pause_start']) && !empty($day['pause_end'])) {
    //                $day['format_pause_start'] =  My_DateTime::formatTime($day['pause_start']);
    //                $day['format_pause_end']   =  My_DateTime::formatTime($day['pause_end']);
    //            }
    //            $status = array_merge($day, $status);
            }
        }
        return $workIntervals;
    }

    public function getWorkInterval($id)
    {
        $workInterval = $this->_modelDbWork->getWorkIntervals($id);
        if (!empty($workInterval['time_start']) && !empty($workInterval['time_end'])) {
            $planning = new Application_Model_Planning();
            $workInterval    = array_merge($workInterval, $planning->_splitStartEndTimeString($workInterval['time_start'], $workInterval['time_end']));
//            if (!empty($day['pause_start']) && !empty($day['pause_end'])) {
//                $day['format_pause_start'] =  My_DateTime::formatTime($day['pause_start']);
//                $day['format_pause_end']   =  My_DateTime::formatTime($day['pause_end']);
//            }
//            $status = array_merge($day, $status);
        }
        return $workInterval;
    }

    public function setWorkIntervals($timeStart,$timeEnd,$colorHex,$description,$id = null)
    {
        return $this->_modelDbWork->setWorkIntervals($timeStart,$timeEnd,$colorHex,$description,$id);
    }

    public function deleteWorkInterval($id)
    {
        return $this->_modelDbWork->deleteWorkInterval($id);
    }

    public function getPauseIntervals($id = null)
    {
        return $this->_modelDbPause->getPauseIntervals($id);
    }

    public function setPauseIntervals($timeStart,$timeEnd,$timeIntervalId,$id = null)
    {
        return $this->_modelDbPause->setPauseIntervals($timeStart,$timeEnd,$timeIntervalId,$id);
    }

    public function deletePauseInterval($id)
    {
        return $this->_modelDbPause->deletePauseInterval($id);
    }



}