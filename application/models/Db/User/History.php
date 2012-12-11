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
                array("*",
                    "total" => "TIMEDIFF(uh.work_hours, uh.missing_hours)"))
            ->where('uh.user_id = ?', $userId)
            ->where('uh.group_id = ?', $groupId)
            ->where('uh.week = ?', $week)
            ->where('uh.year = ?', $year);
        $result = $this->_db->fetchRow($select);
        return $result;
    }



    public function addUserWeekData($dayHistoryData)
    {
        echo "<pre>"    ;
        var_dump($dayHistoryData);
        $select = $this->_db->select()
            ->from(array('uh' => self::TABLE_NAME), array('*'))
            ->where('uh.user_id = ?', $dayHistoryData['user_id'])
            ->where('uh.group_id = ?', $dayHistoryData['group_id'])
            ->where('uh.week = ?', $dayHistoryData['week'])
            ->where('uh.year = ?', $dayHistoryData['year']);
        $result = $this->_db->fetchRow($select);
        if (empty($result)) {
            $this->_db->insert(self::TABLE_NAME,$dayHistoryData);
        } else {
           /* $this->_db->update(
                self::TABLE_NAME,
                array('work_hours' => "addtime(work_hours, '" . $dayHistoryData['work_hours'] . "')" ,
                     'overtime_hours' => "addtime(`overtime_hours`, '20:00:00')"
                ),
                'user_id = ' . $dayHistoryData['user_id']
            );
             */
             $this->_db->query(
                 "UPDATE " . self::TABLE_NAME
                  . " SET
                        work_hours     = ADDTIME(work_hours, '"     . $dayHistoryData['work_hours'] . "'),
                        overtime_hours = ADDTIME(overtime_hours, '" . $dayHistoryData['overtime_hours'] . "'),
                        vacation_hours = ADDTIME(vacation_hours, '" . $dayHistoryData['vacation_hours'] . "'),
                        missing_hours  = ADDTIME(missing_hours, '"  . $dayHistoryData['missing_hours'] . "')
                  WHERE
                        user_id      = " . $dayHistoryData['user_id'] . "
                        AND group_id = " . $dayHistoryData['group_id'] . "
                        AND week     = " . $dayHistoryData['week'] . "
                        AND year     = " . $dayHistoryData['year'] );
        }
        /*$this->_db->delete(self::TABLE_NAME, array(
            'user_id = ?'  => $dayPlan['user_id'],
            'group_id = ?' => $dayPlan['group_id'],
            'week = ?'     => $dayPlan['week'],
            'year = ?'     => $dayPlan['year'])
        );*/
    }
}