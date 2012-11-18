<?php

class Application_Model_Status extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_Status();
    }

    public function getDataById($statusId)
    {
         return $this->_modelDb->getDataById($statusId);
    }

    public function getAllstatus()
    {
        $statuses =  $this->_modelDb->getAllStatus();
        $statusesNormalized = array();
        foreach($statuses as $status) {
            $statusesNormalized[$status['id']] = $status;
        }
        return $statusesNormalized;
    }

    public function saveStatus($values)
    {
        $result = false;
        if (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) {

        }
        //var_dump($values);
        $result = $this->_modelDb->saveStatus($values);
        return $result;
    }

}