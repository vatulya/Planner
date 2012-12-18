<?php

class Application_Model_Db_User_History extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_history';

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserWeekDataByWeekYear($userId, $groupId, $week, $year)
    {
        $select = $this->_db->select()
            ->from(array('uh' => self::TABLE_NAME),
                array("*"))
            ->where('uh.user_id = ?', $userId)
            ->where('uh.group_id = ?', $groupId)
            ->where('uh.week = ?', $week)
            ->where('uh.year = ?', $year);
        $result = $this->_db->fetchRow($select);
        return $result;
    }

    public function addUserWeekData($dayHistoryData)
    {
        $checkExistWeekData = $this->getUserWeekDataByWeekYear(
            $dayHistoryData['user_id'],
            $dayHistoryData['group_id'],
            $dayHistoryData['week'], $dayHistoryData['year']
        );
        if (empty($checkExistWeekData)) {
            $this->_db->insert(self::TABLE_NAME,$dayHistoryData);
        } else {
             $this->_db->query(
                 "UPDATE " . self::TABLE_NAME
                  . " SET
                        work_time     = work_time + '"     . $dayHistoryData['work_time'] . "',
                        overtime_time = overtime_time + '" . $dayHistoryData['overtime_time'] . "',
                        vacation_time = vacation_time + '" . $dayHistoryData['vacation_time'] . "',
                        missing_time  = missing_time + '"  . $dayHistoryData['missing_time'] . "'
                  WHERE
                        user_id      = " . $dayHistoryData['user_id'] . "
                        AND group_id = " . $dayHistoryData['group_id'] . "
                        AND week     = " . $dayHistoryData['week'] . "
                        AND year     = " . $dayHistoryData['year']
             );
        }
    }

    public function updateUserWeekData($userId, $groupId, $week, $year)
    {

    }
}