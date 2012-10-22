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
        return $this->_modelDb->getAllStatus();
    }

}