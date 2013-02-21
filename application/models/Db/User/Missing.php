<?php

class Application_Model_Db_User_Missing extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_missing';

    public function getUserDayMissingPlanByDate($userId, $date, $statusId = false)
    {
        $select = $this->_db->select()
            ->from( array('um' => self::TABLE_NAME), array('*') )
            ->where('um.user_id = ?', $userId)
            ->where('um.date = ?', $date);
        if ($statusId) {
            $select->where('um.status = ?', $statusId);
        }
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function saveUserMissingDay($missingData)
    {
        $this->_db->delete(self::TABLE_NAME, array('user_id = ?' => $missingData['user_id'], 'date = ?' => $missingData['date']));
        $result = $this->_db->insert(self::TABLE_NAME, $missingData);
        return $result;
    }

}