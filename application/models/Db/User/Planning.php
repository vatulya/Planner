<?php

class Application_Model_Db_User_Planning extends Application_Model_Db_Abstract
{
    const TABLE_NAME = 'user_planning';

    const STATUS_DAY_WHITE = '1';
    const STATUS_DAY_GREEN = '2';
    const STATUS_DAY_YELLOW = '3';

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
            $userRequest = new Application_Model_Db_User_Requests();
            $approveUserDayRequest = $userRequest->getAllByUserId($userId, Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $date);
            if ($result['status1'] == self::STATUS_DAY_GREEN) {
                $result['status1'] = $status->getDataById(self::STATUS_DAY_GREEN);
                if (!empty($result['time_start']) && !empty($result['time_end'])) {
                    $date = date_create($result['time_start']);
                    $result['time_start'] = date_format($date, 'H:i');
                    $date = date_create($result['time_end']);
                    $result['time_end'] = date_format($date, 'H:i');
                }
                if (!empty($result['status2'])) {
                    $result['status2'] = $status->getDataById($result['status2']);
                }  else {
                    $result['status_color2'] = "";
                }
            } elseif (!empty($approveUserDayRequest)) {
                $result['status1'] = $status->getDataById(self::STATUS_DAY_YELLOW);
                $result = $this->_resetTimeAndSecondStatus($result);
            } else {
                $result['status1'] = $status->getDataById($result['status1']);
                $result = $this->_resetTimeAndSecondStatus($result);
            }
        }
        return $result;
    }

    private function _resetTimeAndSecondStatus($userDayPlan) {
        unset($userDayPlan['time_start']);
        unset($userDayPlan['time_end']);
        unset($userDayPlan['time_start2']);
        unset($userDayPlan['time_end2']);
        unset($userDayPlan['status2']);
        return $userDayPlan;
    }

    public function getUserNewDayPlanByGroup($userId, $groupId, $date)
    {
        $currentDate = new My_DateTime($date);
        $dateWeekYear = My_DateTime::getWeekYear($currentDate->getTimestamp());
        $weekType = My_DateTime::getEvenWeek($dateWeekYear['week']);
        $groupPlanning = new Application_Model_Db_Group_Plannings();
        $weekGroupPlanning = $groupPlanning->getGroupPlanning($groupId, $weekType, $dateWeekYear['day']);
        if (!empty($weekGroupPlanning[0])) {
            $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $date;
        $userRequest = new Application_Model_Db_User_Requests();
        $approveUserDayRequest = $userRequest->getAllByUserId($userId, Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $date);
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])) {
            $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            if (empty($approveUserDayRequest)) {
                $dayPlan['status1'] = self::STATUS_DAY_GREEN;
                $dayPlan['time_start'] = $weekGroupPlanning['time_start'];
                $dayPlan['time_end'] = $weekGroupPlanning['time_end'];
            } else {
                $dayPlan['status1'] = self::STATUS_DAY_YELLOW;
            }
        }
        $this->_db->insert(self::TABLE_NAME,$dayPlan);
        $result = $this->getUserDayPlanByGroup($userId, $groupId, $date);
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
