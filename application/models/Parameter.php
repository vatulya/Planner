<?php

class Application_Model_Parameter extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Parameters();
    }

    public function getDefaultOpenFreeHours()
    {
        $value = $this->_modelDb->getDefaultOpenFreeHours();
        return $value;
    }

    public function setDefaultOpenFreeHours($value)
    {
        $oldValue = $this->getDefaultOpenFreeHours();
        $result = $this->_modelDb->setDefaultOpenFreeHours($value);
        // RECALCULATE ALL USERS OPEN FREE HOURS. maybe this code is wrong. Need ask customer.
        $this->recalculateAllUsersOpenFreeHours($oldValue);
        return $result;
    }

    public function recalculateAllUsersOpenFreeHours($oldValue)
    {
        $newValue = $this->getDefaultOpenFreeHours();
        $deltaPercentage = $newValue / $oldValue;
        $modelUser = new Application_Model_User();
        $users = $modelUser->getAllUsers();
        foreach ($users as $user) {
            $parameters = $modelUser->getParametersByUserId($user['id']);
            $newOpenFreeHours = $parameters['open_free_hours'];
            $newOpenFreeHours = $newOpenFreeHours * $deltaPercentage; // 216 * 0.8 = 172.8
            $newOpenFreeHours = sprintf('%01.2f', $newOpenFreeHours);
            $modelUserParameters = new Application_Model_Db_User_Parameters();
            $modelUserParameters->setOpenFreeHours($user['id'], $newOpenFreeHours);
        }
    }
}