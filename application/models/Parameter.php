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
        $result = $this->_modelDb->setDefaultTotalFreeHours($value);
        return $result;
    }
}