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
            ->where('uh.week = ?', $week)
            ->where('uh.year = ?', $year);
        if ($userId !== false && $groupId !== false) {
            $select->where('uh.user_id = ?', $userId)
                   ->where('uh.group_id = ?', $groupId);
            $result = $this->_db->fetchRow($select);
        } else {
            $result = $this->_db->fetchAll($select);
        }
        return $result;
    }

    public function getUserHistoryDataByYear($userId, $year)
    {
        $select = $this->_db->select()
            ->from(array('uh' => self::TABLE_NAME),
                array(
                    '*',
                    'work_time' => 'SUM(work_time)',
                    'overtime_time' => 'SUM(overtime_time)',
                    'vacation_time' => 'SUM(vacation_time)',
                    'missing_time' => 'SUM(missing_time)',
                )
            )
            ->where('uh.user_id = ?', $userId)
            ->where('uh.year = ?', $year)
            ->group('uh.user_id');
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
            //TODO make quote for data or use native metod update
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

    public function updateHistoryWeekHour($userId, $groupId, $field, $value, $year, $week)
    {
        $checkExistWeekData = $this->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        if (empty($checkExistWeekData)) {
            //TODO if need make update future time
            //$this->_db->insert(self::TABLE_NAME,$dayHistoryData);
        } else {
            $data = array($field => $value);
            $where = array(
                'user_id = ?'  => $userId,
                'group_id = ?' => $groupId,
                'week = ?'     => $week,
                'year = ?'     => $year,
            );

            $result = $this->_db->update(self::TABLE_NAME, $data, $where);
            return $result;
        }
        return true;
    }
}