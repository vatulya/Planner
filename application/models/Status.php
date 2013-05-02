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

    public function saveStatus($statusData)
    {
        //var_dump($values);
        $statusDataForSave = array(
            'id'               => $statusData['id'],
            'color_hex'        => $statusData['color'],
            'color'            => $statusData['color'],
            'description'      => $statusData['description'],
            'long_description' => $statusData['long_description'],
            'is_holiday'       => $statusData['is_holiday'],
            'alert_description'=> $statusData['alert_description'],
        );
        $result = $this->_modelDb->saveStatus($statusDataForSave);
        return $result;
    }

}