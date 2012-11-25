<?php

class Application_Model_Planning extends Application_Model_Abstract
{
    const STATUS_DAY_WHITE    = '1';
    const STATUS_DAY_GREEN    = '2';
    const STATUS_DAY_YELLOW   = '3';
    const STATUS_DAY_RED      = '4';
    const STATUS_DAY_CYAN     = '5';
    const STATUS_DAY_BLUE     = '6';
    const STATUS_DAY_OVERTIME = '7';

    protected $_modelDb;

    public function __construct()
    {
        $this->_modelDb = new Application_Model_Db_User_Planning();
        $this->_modelMissing = new Application_Model_Missing();
    }

    public function getUserWeekPlanByGroup($userId, $groupId,  $year, $week)
    {
        $weekPlan = array();
        $weekDays = My_DateTime::getWeekDays();
        $dateWeekStart = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $currentDate = new My_DateTime();
        $currentDate = $currentDate->getTimestamp();
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $groupPlanning = new Application_Model_Group();


        foreach ($weekDays as $numDay=>$nameDay) {
        $date = clone $dateWeekStart;
        $date->modify('+' . $numDay . 'day');
        $dateFormat = $date->format('Y-m-d');
        // echo $date->getTimestamp() . "----" . $currentDate . "----"  .$dateFormat. "<br>";
        if ($date->getTimestamp() >= $currentDate) {
        $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $dateFormat);
        if (!empty($weekGroupPlanning[0])) {
        $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $dateFormat;
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])) {
          $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            $dayPlan['status1'] = self::STATUS_DAY_GREEN;
            $dayPlan['time_start'] = $weekGroupPlanning['time_start'];
            $dayPlan['time_end'] = $weekGroupPlanning['time_end'];
        }
        $weekPlan[$nameDay] = $this->_setStatusByRules($dayPlan);
        //  echo "<pre> - $groupId  ----day " . $dateFormat ;
                      //  var_dump($weekPlan[$nameDay]);

            }  else {
                $weekPlan[$nameDay] = $this->_setStatusByRules($weekUserPlan->getUserDayPlanByGroup($userId, $groupId, $dateFormat));
            }
        }
        return $weekPlan;
    }

    public function createNewDayUserPlanByGroup($userId, $groupId, $date)
    {
        $groupPlanning = new Application_Model_Group();
        $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $date);
        if (!empty($weekGroupPlanning[0])) {
            $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $date;
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])) {
            $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            $dayPlan['status1'] = self::STATUS_DAY_GREEN;
            $dayPlan['time_start'] = $weekGroupPlanning['time_start'];
            $dayPlan['time_end'] = $weekGroupPlanning['time_end'];
        }
        $planning = new Application_Model_Db_User_Planning();
        $planning->createNewDayUserPlanByGroup($dayPlan);
    }

    private function _setStatusByRules($result)
    {
        if(!empty($result)) {
            $status = new Application_Model_Status();
            $userRequest = new Application_Model_Db_User_Requests();
            $userMissing = new Application_Model_Db_User_Missing();
            $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
            $missingUserDayStatuses = $userMissing->getUserDayMissingPlanByDate($result['user_id'], $result['date']);
            if ($result['status1'] == self::STATUS_DAY_GREEN) {
                if (!empty($approveUserDayRequest)) {
                    $result['status1'] = $status->getDataById(self::STATUS_DAY_YELLOW);
                    $result = $this->_resetTimeAndSecondStatus($result);
                } else {
                    $result['status1'] = $status->getDataById(self::STATUS_DAY_GREEN);
                    if (!empty($result['time_start']) && !empty($result['time_end'])) {
                        $date = date_create($result['time_start']);
                        $result['time_start'] = date_format($date, 'H:i');
                        $date = date_create($result['time_end']);
                        $result['time_end'] = date_format($date, 'H:i');
                    }
                    if (!empty($missingUserDayStatuses)) {
                        $result['status2'] = $status->getDataById($missingUserDayStatuses['status']);
                    }  else {
                        $result['status_color2'] = "";
                    }
                }
            } else {
                $result['status1'] = $status->getDataById($result['status1']);
                $result = $this->_resetTimeAndSecondStatus($result);
                $overtime = $this->getUserDayOvertimeByDate($result['user_id'], $result['group_id'], $result['date']);
                if (!empty($overtime)) {
                    $result['time_start2'] = $overtime['time_start'] ;
                    $result['time_end2']   = $overtime['time_end'];
                    $result['status2'] = $status->getDataById(self::STATUS_DAY_OVERTIME);
                }
            }
        } else {
            $result = $this->_setDefaultStatusForEmptyPlan();
        }
        return $result;
    }

    private function _setDefaultStatusForEmptyPlan() {
        $status = new Application_Model_Status();
        $result['status1'] = $status->getDataById(self::STATUS_DAY_WHITE);
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

    public function getUserWorkTimeByGroup($userId, $groupId,  $year, $week)
    {
        $dateWeekStart = new DateTime($year . 'W' . sprintf("%02d", $week));
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $date = $dateWeekStart->format('Y-m-d');
        $workTime = $weekUserPlan->getTotalWorkTimeByGroup($userId, $groupId, $date);

        return $workTime;
    }

    public function getUserDayById($dayId)
    {
        $userDay = new Application_Model_Db_User_Planning();
        $result = $userDay->getDayById($dayId);
        $userRequest = new Application_Model_Db_User_Requests();
        $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
        if ($result['status1'] == self::STATUS_DAY_GREEN && !empty($approveUserDayRequest)) {
            $result['status1'] = self::STATUS_DAY_YELLOW;
            $result = $this->_resetTimeAndSecondStatus($result);
        }
        return $result;
    }

    public function saveDayAdditionalUserStatus($formData)
    {
        $status = $formData['status'];
        if ($status == self::STATUS_DAY_RED || $status == self::STATUS_DAY_CYAN || $status == self::STATUS_DAY_BLUE) {
            return $this->_modelMissing->saveUserMissingDay($formData);
        } elseif ($status == self::STATUS_DAY_OVERTIME) {
            var_dump($formData);
            //TODO save overtime
            return true;
        }
        var_dump($formData);
        return false;
    }

    public function getUserDayOvertimeByDate($userId, $groupId, $date)
    {
        $modelOvertime = new Application_Model_Db_User_Overtime();
        return $modelOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
    }
}