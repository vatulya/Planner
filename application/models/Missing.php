<?php

class Application_Model_Missing extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Missing();
    }

    public function getUserDayMissingPlanByDate($userId, $date, $statusId = false)
    {
        $missings = $this->_modelDb->getUserDayMissingPlanByDate($userId, $date, $statusId);
        if(!empty($missings['time_start']) && !empty($missings['time_end'])) {
            $workSeconds = Application_Model_Day::getWorkHoursByMarkers(
                $missings['time_start'],
                $missings['time_end'],
                "00:00:00","00:00:00"
            );
            $missings['total_time'] =  $workSeconds;
        }
        return $missings;
    }

    public function saveUserMissingDay($formData)
    {
        $missingDayData = array(
            "user_id"    => $formData['user_id'],
            "status"     => $formData['status'],
            "time_start" => $formData['time_start'],
            "time_end"   => $formData['time_end'],
            "date"       => $formData['date']
        );
        $result = true;
        if (!empty($formData['status'])  && !empty($formData['user_id']) && !empty($formData['date'])) {
            $this->_modelDb->deleteUserMissingDay($missingDayData);
        } else {
            return false;
        }
        if ( !empty($formData['time_start']) && !empty($formData['time_end'])) {
            $result = $this->_modelDb->saveUserMissingDay($missingDayData);
        }
        return $result;
    }
}