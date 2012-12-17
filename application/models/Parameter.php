<?php

class Application_Model_Parameter extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Parameters();
    }

    public function getDefaultWorkHours()
    {
        return 40; // this is as default now
    }

    public function getDefaultTotalFreeHours()
    {
        $value = $this->_modelDb->getDefaultTotalFreeHours();
        return $value;
    }

    public function setDefaultTotalFreeHours($value)
    {
        $oldValue = $this->getDefaultTotalFreeHours();
        $result = $this->_modelDb->setDefaultTotalFreeHours($value);
        // RECALCULATE ALL USERS TOTAL FREE HOURS. maybe this code is wrong. Need ask customer.
        $deltaPercentage = $this->getDefaultTotalFreeHours() / $oldValue; // 108 / 216 = 0.5
        $this->recalculateAllUsersTotalFreeTime($deltaPercentage);
        return $result;
    }

    public function recalculateAllUsersTotalFreeTime($deltaPercentage, $userId = 0)
    {
        $modelUser           = new Application_Model_User();
        $modelUserParameters = new Application_Model_Db_User_Parameters();

        if ($userId > 0) {
            $users = array($userId);
        } else {
            $users = $modelUser->getAllUsers();
        }

        foreach ($users as $user) {
            $userId = $user['id'];
            $parameters = $modelUser->getParametersByUserId($userId);

            if ($parameters['regular_work_hours'] == 40) {
                $modelUserParameters->setTotalFreeTime($userId, $this->getDefaultTotalFreeHours() * 3600); // just set Default total free hours
            } else {
                $newTotalFreeTime = $parameters['total_free_time'];
                $newTotalFreeTime = $newTotalFreeTime * $deltaPercentage; // 216 * 0.8 = 172.8
                $modelUserParameters->setTotalFreeTime($userId, $newTotalFreeTime);
            }

            $newAllowedFreeTime = $parameters['allowed_free_time'];
            $newAllowedFreeTime = $newAllowedFreeTime * $deltaPercentage; // 216 * 0.8 = 172.8
            $modelUserParameters->setAllowedFreeTime($userId, $newAllowedFreeTime);
        }
        return true;
    }

}