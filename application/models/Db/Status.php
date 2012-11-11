<?php

class Application_Model_Db_Status extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'status';

    public function getDataById($statusId)
    {
        $select = $this->_db->select()
            ->from(array('s' => self::TABLE_NAME))
            ->where('s.id = ?', $statusId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

     public function getAllStatus()
     {
         $select = $this->_db->select()
             ->from(array('s' => self::TABLE_NAME));
         $result = $this->_db->fetchAll($select);
         return $result;
     }

     public function saveStatus()
     {

         return true;
     }
}