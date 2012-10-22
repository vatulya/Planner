<?php

class Application_Model_Db_User_Planning extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_planning';

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserDayPlanByGroup($userId, $groupId, $date)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.user_id = ?', $userId)
            ->where('up.group_id = ?', $groupId)
            ->where('up.date = ?', $date);
            //->where('up.date <= ADDDATE( ?, INTERVAL 6 DAY)', $date)
        $result = $this->_db->fetchRow($select);
        if(!empty($result)) {
            $status = new Application_Model_Status();
            $date = date_create($result['time_start']);
            $result['time_start'] = date_format($date, 'H:i');
            $date = date_create($result['time_end']);
            $result['time_end'] = date_format($date, 'H:i');
            $result['status1'] = $status->getDataById($result['status1']);
            if (!empty($result['status2'])) {
                $result['status2'] = $status->getDataById($result['status2']);
            }  else {
                $result['status_color2'] = "";
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

    public function getDayById($dayId)
    {
        $select = $this->_db->select()
            ->from(array('up' => self::TABLE_NAME))
            ->where('up.id = ?', $dayId);
        $result = $this->_db->fetchRow($select);
        return $result;
    }
}
