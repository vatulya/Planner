<?php

class Application_Model_Db_User_Overtime extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_overtime';

    public function getUserDayOvertimeByDate($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('uo' => self::TABLE_NAME), array('*', 'total_time' => 'TIMEDIFF(time_end,time_start)'))
            ->where('uo.user_id = ?', $userId)
            ->where('uo.group_id = ?', $groupId)
            ->where('uo.date = ?', $date);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function saveUserDayOvertimeByDate($overtimeData)
    {
        $result = $this->_db->insert(self::TABLE_NAME, $overtimeData);
        return $result;
    }

    public function deleteOvertime($overtimeData)
    {
        $this->_db->delete(self::TABLE_NAME, array('user_id = ?' => $overtimeData['user_id'], 'group_id = ?' => $overtimeData['group_id'], 'date = ?' => $overtimeData['date']));
    }
}