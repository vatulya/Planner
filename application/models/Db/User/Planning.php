<?php

class Application_Model_Db_User_Planning extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_planning';

    private $_status = array(0 => '009900', 1 => 'FFFFFF', 2 => 'FFFF00', 3 => 'FF0000', 4 => '00FFFF', 5 => '0000FF');

    public function getUserWeekPlanByGroup($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId)
            ->where('up.group_id = ?', $groupId)
            ->where('up.date = ?', $date);
            //->where('up.date <= ADDDATE( ?, INTERVAL 6 DAY)', $date)
        $result = $this->_db->fetchRow($select);
        if(!empty($result)) {
            $date = date_create($result['time_start']);
            $result['time_start'] = date_format($date, 'H:i');
            $date = date_create($result['time_end']);
            $result['time_end'] = date_format($date, 'H:i');
            $result['status_color1'] = $this->_status[$result['status1']];
            if (!empty($result['status_color2'])) {
                $result['status_color2'] = $this->_status[$result['status2']];
            }
        }
        return $result;
    }

    public function getTotalWorkTimeByGroup($userId, $groupId, $date, $weekLenght = 6)
    {
        $select = $this->_db->select()
            ->from(
                array('up' => self::TABLE_NAME),
                array("SUM( TIME_FORMAT(TIMEDIFF(up.time_end, up.time_start), '%H') )")
            )
            ->where('up.user_id = ?', $userId)
            ->where('up.group_id = ?', $groupId)
            ->where('up.status1 = ?', 0)
            ->where('up.date >= ?', $date)
            ->where('up.date <= ADDDATE( ?, INTERVAL ' . $weekLenght . ' DAY)', $date);
        $result = $this->_db->fetchOne($select);
        if (empty($result)) {
            $result = 0;
        }
        return $result;
    }
}
