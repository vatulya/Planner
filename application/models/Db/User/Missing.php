<?php

class Application_Model_Db_User_Missing extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_missing';

    public function getUserDayMissingPlanByDate($userId, $date)
    {
        $select = $this->_db->select()
            ->from(array('um' => self::TABLE_NAME))
            ->where('um.user_id = ?', $userId)
            ->where('um.date = ?', $date);
        //->where('up.date <= ADDDATE( ?, INTERVAL 6 DAY)', $date)
        $result = $this->_db->fetchRow($select);
        return $result;
    }
}