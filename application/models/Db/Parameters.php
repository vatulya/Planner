<?php

class Application_Model_Db_Parameters extends Application_Model_Db_Abstract
{

    const TABLE_NAME = 'parameters';

    public function getDefaultTotalFreeHours()
    {
        $select = $this->_db->select()
            ->from(array('p' => self::TABLE_NAME), 'default_total_free_hours');
        $result = $this->_db->fetchOne($select);
        return $result;
    }

     public function setDefaultTotalFreeHours($value)
     {
         try {
             $check = $this->getDefaultTotalFreeHours();
             if ($check == $value) {
                 $result = 1;
             } else {
                 $data = array(
                     'default_total_free_hours' => $value,
                 );
                 $result = $this->_db->update(self::TABLE_NAME, $data);
             }
         } catch (Exception $e) {
             throw new Exception('Error! Database error!');
         }
         return $result;
     }


}