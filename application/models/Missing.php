<?php

class Application_Model_Request extends Application_Model_Abstract
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
}