<?php

class Application_Model_Missing extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Missing();
    }

    public function getUserDayMissingPlanByDate($userId, $date)
    {
        $missings = $this->_modelDb->getUserDayMissingPlanByDate($userId, $date);
        return $missings;
    }

    public function saveUserMissingDay($formData)
    {
        //TODO add check role
        if (empty($formData['status2']) || empty($formData['time_start2']) || empty($formData['time_end2']) || empty($formData['user_id']) || empty($formData['date'])) {
            return false;
        }
        $missingDayData = array(
            "user_id"    => $formData['user_id'],
            "status"     => $formData['status2'],
            "time_start" => $formData['time_start2'],
            "time_end"   => $formData['time_end2'],
            "date"       => $formData['date']
        );
        $result = $this->_modelDb->saveUserMissingDay($missingDayData);
        return $result;
    }
}