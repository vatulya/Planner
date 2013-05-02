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


     public function saveStatus($statusData)
     {
         //$this->_db->delete(self::TABLE_NAME, array('id = ?' => $statusData['id']));
         //$result = $this->_db->insert(self::TABLE_NAME, $statusData);
         $result = $this->_db->update(self::TABLE_NAME,
             array(
                 'color_hex'        => $statusData['color'],
                 'color'            => $statusData['color'],
                 'description'      => $statusData['description'],
                 'long_description' => $statusData['long_description'],
                 'is_holiday'       => $statusData['is_holiday'],
                 'alert_description'=> $statusData['alert_description'],
             ),
             'id = ' . (int)$statusData['id']
         );
         return $result;
     }


}