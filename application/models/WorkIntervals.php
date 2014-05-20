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
            }
        }
        return $workIntervals;
    }

    public function getWorkInterval($id)
    {
        $workInterval = $this->_modelDbWork->getWorkIntervals($id);
        if (!empty($workInterval['time_start']) && !empty($workInterval['time_end'])) {
            $workInterval = array_merge($workInterval, $this->splitStartEndTimeString($workInterval['time_start'], $workInterval['time_end']));
        }
        return $workInterval;
    }

    public function saveWorkInterval($data)
    {
        $timeStart = $this->getConcatTimeString($data['time_start_hour'],$data['time_start_min']);
        $timeEnd = $this->getConcatTimeString($data['time_end_hour'],$data['time_end_min']);
        return $this->_modelDbWork->setWorkInterval($timeStart,$timeEnd,$data['color_hex'],$data['description'],@$data['id']);
    }

    public function deleteWorkInterval($id)
    {
        return $this->_modelDbWork->deleteWorkInterval($id);
    }

    public function getPauseIntervals()
    {
        $planning = new Application_Model_Planning();
        $workIntervals = $this->_modelDbPause->getPauseIntervals();
        foreach ($workIntervals as &$workInterval) {
            if (!empty($workInterval['time_start']) && !empty($workInterval['time_end'])) {
                $workInterval    = array_merge($workInterval, $planning->_splitStartEndTimeString($workInterval['time_start'], $workInterval['time_end']));
            }
        }
        return $workIntervals;
    }

    public function getPauseInterval($id = null)
    {
        $workInterval = $this->_modelDbPause->getPauseIntervals($id);
        if (!empty($workInterval['time_start']) && !empty($workInterval['time_end'])) {
            $workInterval = array_merge($workInterval, $this->splitStartEndTimeString($workInterval['time_start'], $workInterval['time_end']));
        }
        return $workInterval;
    }

    public function savePauseInterval($data)
    {
        $timeStart = $this->getConcatTimeString($data['time_start_hour'],$data['time_start_min']);
        $timeEnd = $this->getConcatTimeString($data['time_end_hour'],$data['time_end_min']);
        return $this->_modelDbPause->savePauseInterval($timeStart,$timeEnd,$data['id']);
    }

    public function deletePauseInterval($id)
    {
        return $this->_modelDbPause->deletePauseInterval($id);
    }


    public function splitStartEndTimeString($timeStart, $timeEnd)
    {
        $planning = new Application_Model_Planning();
        $intervals = $planning->_splitStartEndTimeString($timeStart, $timeEnd);
        $intervals['time_start_hour'] = $intervals['split_time_start']['hour'];
        $intervals['time_start_min'] = $intervals['split_time_start']['min'];
        $intervals['time_end_hour'] = $intervals['split_time_end']['hour'];
        $intervals['time_end_min'] = $intervals['split_time_end']['min'];
        return $intervals;
    }

    private function getConcatTimeString($start, $end)
    {
        return $start . ':' . $end;
    }
}