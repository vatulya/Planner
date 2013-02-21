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
        $this->_modelMissing  = new Application_Model_Missing();
        $this->_modelOvertime = new Application_Model_Db_User_Overtime();
    }

    public function getUserWeekPlanByGroup($userId, $groupId,  $year, $week)
    {
        $weekPlan = array();
        $weekDays = My_DateTime::getWeekDays();
        $dateWeekStart = new My_DateTime($year . 'W' . sprintf("%02d", $week));

        foreach ($weekDays as $numDay=>$nameDay) {
            $date = clone $dateWeekStart;
            $date->modify('+' . $numDay . 'day');
            $dateFormat = $date->format('Y-m-d');
            $weekPlan[$nameDay] = $this->getDayGroupUserPlanByDate($userId, $groupId, $dateFormat) ;
        }
        return $weekPlan;
    }

    public function getDayGroupUserPlanByDate($userId, $groupId, $dateFormat)
    {
        $currentDate = new My_DateTime();
        $currentDate = $currentDate->getTimestamp();
        $date = new My_DateTime($dateFormat);
        //Get from history or future(group plan) day plan
        if ($date->getTimestamp() >= $currentDate) {
            $dayPlan = $this->_getUserDayPlanFromGroupPlan($userId, $groupId, $dateFormat);
            return $this->_setStatusByRules($dayPlan);
        }  else {
            $dayPlan = $this->getUserDayPlanFromPlanning($userId, $groupId, $dateFormat);
            return $this->_setStatusByRules($dayPlan);
        }
    }

    public function getUserPlanningByDate($userId, $groupId, $dateFormat)
    {
        $currentDate = new My_DateTime();
        $currentDate = $currentDate->getTimestamp();
        $date = new My_DateTime($dateFormat);
        //Get from history or future(group plan) day plan
        if ($date->getTimestamp() >= $currentDate) {
            $groupPlanning = new Application_Model_Group();
            $dayPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $userId, $dateFormat);
            $dayPlanning = $dayPlanning[0];
        }  else {
            $dayPlanning = $this->getUserDayPlanFromPlanning($userId, $groupId, $dateFormat);
        }
        return $dayPlanning;
    }

    public function getUserDayPlanFromPlanning($userId, $groupId, $dateFormat)
    {
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $dayPlan = $weekUserPlan->getUserDayPlanByGroup($userId, $groupId, $dateFormat);
        if(!empty($dayPlan['time_start']) && !empty($dayPlan['time_end'])) {
            $workSeconds = Application_Model_Day::getWorkHoursByMarkers(
                $dayPlan['time_start'],
                $dayPlan['time_end'],
                $dayPlan['pause_start'],
                $dayPlan['pause_end']
            );
            $dayPlan['total_time'] =  $workSeconds;
        }
        return $dayPlan;
    }

    private function _getUserDayPlanFromGroupPlan($userId, $groupId, $dateFormat)
    {
        $groupPlanning = new Application_Model_Group();
        $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $userId, $dateFormat);
        if (!empty($weekGroupPlanning[0])) {
            $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $dateFormat;
        $dayPlan['editable'] = $this->_getEditableDay($userId, $groupId);
        $groupExceptions = new Application_Model_Db_Group_Exceptions();
        $groupForDateException = $groupExceptions->checkExceptionByDate($groupId, $dateFormat);
        $holidays = new Application_Model_Db_Group_Holidays();
        $holidayForDate = $holidays->checkHolidayByDate($dateFormat);
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])
            || !empty($groupForDateException) || !empty($holidayForDate)) {
            $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            $dayPlan['status1'] = self::STATUS_DAY_GREEN;
            $dayPlan['time_start']  = $weekGroupPlanning['time_start'];
            $dayPlan['time_end']    = $weekGroupPlanning['time_end'];
            $dayPlan['pause_start'] = $weekGroupPlanning['pause_start'];
            $dayPlan['pause_end']   = $weekGroupPlanning['pause_end'];
            $workSeconds = Application_Model_Day::getWorkHoursByMarkers(
                $dayPlan['time_start'],
                $dayPlan['time_end'],
                $dayPlan['pause_start'],
                $dayPlan['pause_end']
            );
            $dayPlan['total_time'] =  $workSeconds;
        }
        return $dayPlan;
    }

    private function _formatTime($time)
    {
        try {
            $date = date_create($time);
        } catch (Exception $e) {
            return $time;
        }
        return date_format($date, 'H:i');
    }

    private function _getEditableDay($userId, $groupId)
    {
        $auth = new Application_Model_Auth();
        $user = $auth->getCurrentUser();
        $meId = $user['id'];
        $userGroup = new Application_Model_Db_User_Groups();
        $groupsAdmin = $userGroup->getUserGroupsAdmin($meId);
        if ($meId == $userId || (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) || in_array($groupId, $groupsAdmin)) {
            return true;
        }
        return false;
    }

    public function createNewDayUserPlanByGroup($userId, $groupId, $date)
    {
        //TODO use  _getUserDayPlanFromGroupPlan and allow needed fields
        $groupPlanning = new Application_Model_Group();
        $group = new Application_Model_Group();
        $weekGroupPlanning = $groupPlanning->getGroupPlanningByDate($groupId, $userId, $date);
        if (!empty($weekGroupPlanning[0])) {
            $weekGroupPlanning = $weekGroupPlanning[0];
        }
        $dayPlan = array();
        $dayPlan['group_id'] = $groupId;
        $dayPlan['user_id'] = $userId;
        $dayPlan['date'] = $date;
        $groupExceptions = new Application_Model_Db_Group_Exceptions();
        $groupForDateException = $groupExceptions->checkExceptionByDate($groupId, $date);
        $holidays = new Application_Model_Db_Group_Holidays();
        $holidayForDate = $holidays->checkHolidayByDate($date);
        if(empty($weekGroupPlanning['time_start']) || empty($weekGroupPlanning['time_end'])
            || !empty($groupForDateException) || !empty($holidayForDate)) {
            $dayPlan['status1'] = self::STATUS_DAY_WHITE;
        } else {
            $dayPlan['status1'] = self::STATUS_DAY_GREEN;
            $dayPlan['time_start']  = $weekGroupPlanning['time_start'];
            $dayPlan['time_end']    = $weekGroupPlanning['time_end'];
            $dayPlan['pause_start'] = $weekGroupPlanning['pause_start'];
            $dayPlan['pause_end']   = $weekGroupPlanning['pause_end'];
        }
        $planning = new Application_Model_Db_User_Planning();
        $planning->createNewDayUserPlanByGroup($dayPlan);
    }

    private function _setStatusByRules($result)
    {
        $status = new Application_Model_Status();
        $userRequest = new Application_Model_Db_User_Requests();
        $userMissing = new Application_Model_Missing();
        $userOvertime = new Application_Model_Overtime();

        if(!empty($result)) {
            $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
            $missingUserDayStatuses = $userMissing->getUserDayMissingPlanByDate($result['user_id'], $result['date']);
            $overtimeUserDay = $userOvertime->getUserDayOvertimeByDate($result['user_id'], $result['group_id'], $result['date']);

            $statuses = $status->getAllStatus();
            if ($result['status1'] == self::STATUS_DAY_GREEN) {
                if (!empty($result['time_start']) && !empty($result['time_end'])) {
                    $statuses[self::STATUS_DAY_GREEN]['time_start'] = $this->_formatTime($result['time_start']);
                    $statuses[self::STATUS_DAY_GREEN]['time_end'] = $this->_formatTime($result['time_end']);
                    $statuses[self::STATUS_DAY_GREEN]['pause_start'] = $this->_formatTime($result['pause_start']);
                    $statuses[self::STATUS_DAY_GREEN]['pause_end'] = $this->_formatTime($result['pause_end']);
                    $statuses[self::STATUS_DAY_GREEN]['total_time'] = My_DateTime::TimeToDecimal($result['total_time']);
                }
            } else {
                $result['status1'] = $status->getDataById($result['status1']);
                $result = $this->_resetTimeAndSecondStatus($result);
               //$overtime = $this->getUserDayOvertimeByDate($result['user_id'], $result['group_id'], $result['date']);
                if (!empty($overtime)) {
                    $result['time_start2'] = $this->_formatTime($overtime['time_start']) ;
                    $result['time_end2']   = $this->_formatTime($overtime['time_end']);
                    $result['total_time2'] =  My_DateTime::TimeToDecimal($overtime['total_time']);
                    $result['status2'] = $status->getDataById(self::STATUS_DAY_OVERTIME);
                }
            }
            if (!empty($approveUserDayRequest)) {
                $result['status1'] = $status->getDataById(self::STATUS_DAY_YELLOW);
                $result = $this->_resetTimeAndSecondStatus($result);
            } else {

                if (!empty($missingUserDayStatuses)) {
                    $result['status2'] = $status->getDataById($missingUserDayStatuses['status']);
                    $result['time_start2'] = $this->_formatTime($missingUserDayStatuses['time_start']);
                    $result['time_end2'] = $this->_formatTime($missingUserDayStatuses['time_end']);
                    $result['total_time2'] =  My_DateTime::TimeToDecimal($missingUserDayStatuses['total_time']);
                }  else {
                    $overtime = $userOvertime->getUserDayOvertimeByDate($result['user_id'], $result['group_id'], $result['date']);
                    if (!empty($overtime)) {
                        $result['time_start2'] = $this->_formatTime($overtime['time_start']) ;
                        $result['time_end2']   = $this->_formatTime($overtime['time_end']);
                        $result['total_time2'] =  My_DateTime::TimeToDecimal($overtime['total_time']);
                        $result['status2'] = $status->getDataById(self::STATUS_DAY_OVERTIME);
                    } else {
                        $result['status2'] = "";
                    }
                }
            }

            //If the day as now allow edit form
            $date = new My_DateTime();
            $currentDateFormat = $date->format('Y-m-d');
            if ($result['date'] == $currentDateFormat) {
                $result['editable'] = $this->_getEditableDay($result['user_id'], $result['group_id']);
            } elseif (empty($result['editable'])) {
                //user can't chang past data only admins.
                $result['editable'] = $this->_getEditableDay(0, $result['group_id']);
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
        if (!empty($formData['time_start']) && !empty($formData['time_end'])) {
            //TODO realize logic for update
            //$this->_modelDb->saveWorkTime($formData);
        }
        $status = $formData['status2'];
        if ($status == self::STATUS_DAY_RED || $status == self::STATUS_DAY_CYAN || $status == self::STATUS_DAY_BLUE) {
            return $this->_modelMissing->saveUserMissingDay($formData);
        } elseif ($status == self::STATUS_DAY_OVERTIME) {
            return $this->saveUserOvertimeDay($formData);
        }
        return false;
    }


    public function checkExistDay($date)
    {
        $day = $this->_modelDb->checkExistDay($date);
        if (empty($day)) {
            return true;
        }
        return false;
    }

    public function getUserDayStatuses($userId, $groupId, $date)
    {
        $status = new Application_Model_Status();

        $statuses = $status->getAllStatus();
        foreach ($statuses as $statusId => &$status) {
            switch ($statusId) {
                case self::STATUS_DAY_WHITE:
                    break;
                case self::STATUS_DAY_GREEN:
                    $day = $this->getUserPlanningByDate($userId, $groupId, $date);
                    if (!empty($day['time_start']) && !empty($day['time_end'])) {
                        $day    = array_merge($day, $this->_splitStartEndTimeString($day['time_start'], $day['time_end']));
                        $status = array_merge($day, $status);
                    }
                    break;
                case self::STATUS_DAY_YELLOW:
                    $userRequest = new Application_Model_Db_User_Requests();
                    $approveUserDayRequest = $userRequest->getAllByUserId($userId, '', $date);
                    if (!empty($approveUserDayRequest) ) {
                        $status = array_merge($approveUserDayRequest, $status);
                    }
                    break;
                case self::STATUS_DAY_RED:
                    $status = array_merge($this->_getMissingData($userId, $date, self::STATUS_DAY_RED), $status);
                    break;
                case self::STATUS_DAY_CYAN:
                    $status = array_merge($this->_getMissingData($userId, $date, self::STATUS_DAY_CYAN), $status);
                    break;
                case self::STATUS_DAY_BLUE:
                    $status = array_merge($this->_getMissingData($userId, $date, self::STATUS_DAY_BLUE), $status);
                    break;
                case self::STATUS_DAY_OVERTIME:
                    $userOvertime = new Application_Model_Overtime();
                    $overtime = $userOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
                    if (!empty($overtime['time_start']) && !empty($overtime['time_end'])) {
                        $overtime    = array_merge($overtime, $this->_splitStartEndTimeString($overtime['time_start'], $overtime['time_end']));
                        $status = array_merge($overtime, $status);
                    }
                    break;
            }
        }
        return $statuses;
    }

    private function _getMissingData($userId, $date, $status)
    {
        $userMissing = new Application_Model_Missing();
        $missingDay = $userMissing->getUserDayMissingPlanByDate($userId, $date, $status);
        if (!empty($missingDay['time_start']) && !empty($missingDay['time_end'])) {
            return array_merge($missingDay, $this->_splitStartEndTimeString($missingDay['time_start'], $missingDay['time_end']));
        }
        return array();
    }

    private function _splitStartEndTimeString($timeStart, $timeEnd)
    {
        return array(
            'split_time_start' =>  My_DateTime::splitTimeString($timeStart),
            'split_time_end'   =>  My_DateTime::splitTimeString($timeEnd),

        );
    }

    public function getWeekHistory($userId, $groupId, $year, $week)
    {
        $workTime = $this->getUserWorkTimeByGroup($userId, $groupId, $year, $week);
        return $workTime;
    }
}