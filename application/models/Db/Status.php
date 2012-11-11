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

     public function saveStatus($fields)
     {
         $id = $fields['id'];
         unset($fields['id']);
         unset($fields['use_status2']);
         unset($fields['color']);
         $this->_db->update(Application_Model_Db_User_Planning::TABLE_NAME, $fields, array('id = ?' => $id));
         return true;
     }
}