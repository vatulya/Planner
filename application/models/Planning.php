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
        $this->_modelUser     = new Application_Model_User();
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
            $weekPlan[$nameDay] = $this->getUserDayStatuses($userId, $groupId, $dateFormat) ;
        }
        return $weekPlan;
    }

/*    public function getDayGroupUserPlanByDate($userId, $groupId, $dateFormat)
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
            if (empty($dayPlan)) {
                return $this->_setDefaultStatusForEmptyPlan($userId, $groupId, $dateFormat);
            } else {
                return $this->_setStatusByRules($dayPlan);
            }
        }
    }*/

    private function _setDefaultStatusForEmptyPlan($userId, $groupId, $dateFormat) {
        $status = new Application_Model_Status();
        $dayPlan['status1'] = $status->getDataById(self::STATUS_DAY_WHITE);
        $dayPlan['user_id'] = $userId;
        $dayPlan['group_id'] = $groupId;
        $dayPlan['date'] = $dateFormat;

        $date = new My_DateTime();
        $currentDateFormat = $date->format('Y-m-d');
        if ($dateFormat == $currentDateFormat) {
            $dayPlan['editable'] = $this->_getEditableDay($userId, $groupId);
        } else {
            //user can't chang past data only admins.
            $dayPlan['editable'] = $this->_getEditableDay(0, $groupId);
        }
        return $dayPlan;
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
            $dayPlanning = @$dayPlanning[0];
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

    private function _getEditableDay($userId, $groupId)
    {
        $auth = new Application_Model_Auth();
        $user = $auth->getCurrentUser();
        $meId = $user['id'];
        if (!empty($meId)) {
            $userGroup = new Application_Model_Db_User_Groups();
            $groupsAdmin = $userGroup->getUserGroupsAdmin($meId);
            if ($meId == $userId || (Application_Model_Auth::getRole() >= Application_Model_Auth::ROLE_ADMIN) || in_array($groupId, $groupsAdmin)) {
                return true;
            }
        }
        return false;
    }

    public function createNewDayUserPlanByGroup($userId, $groupId, $date)
    {
        //TODO use  _getUserDayPlanFromGroupPlan and allow needed fields
        $groupPlanning = new Application_Model_Group();
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

/*    private function _setStatusByRules($result)
    {
        $status = new Application_Model_Status();
        $userRequest = new Application_Model_Db_User_Requests();
        $userMissing = new Application_Model_Missing();
        $userOvertime = new Application_Model_Overtime();
        $approveUserDayRequest = $userRequest->getAllByUserId($result['user_id'], Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $result['date']);
        $missingUserDayStatuses = $userMissing->getUserDayMissingPlanByDate($result['user_id'], $result['date']);

        $statuses = $status->getAllStatus();
        if ($result['status1'] == self::STATUS_DAY_GREEN) {
            $result['status1'] = $status->getDataById(self::STATUS_DAY_GREEN);
            if (!empty($result['time_start']) && !empty($result['time_end'])) {
                $result['time_start'] = $this->_formatTime($result['time_start']);
                $result['time_end'] = $this->_formatTime($result['time_end']);
                $result['pause_start'] = $this->_formatTime($result['pause_start']);
                $result['pause_end'] = $this->_formatTime($result['pause_end']);
                $result['total_time'] = My_DateTime::TimeToDecimal($result['total_time']);
            }
        } else {
            $result['status1'] = $status->getDataById($result['status1']);
            $result = $this->_resetTimeAndSecondStatus($result);
        }
        if (!empty($approveUserDayRequest)) {
            $result['status1'] = $status->getDataById(self::STATUS_DAY_YELLOW);
            $result = $this->_resetTimeAndSecondStatus($result);
        } else {

            if (!empty($missingUserDayStatuses[0])) {
                $missingUserDayStatuses = $missingUserDayStatuses[0];
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
        return $result;
    }

    private function _resetTimeAndSecondStatus($userDayPlan) {
        unset($userDayPlan['time_start']);
        unset($userDayPlan['time_end']);
        unset($userDayPlan['time_start2']);
        unset($userDayPlan['time_end2']);
        unset($userDayPlan['status2']);
        return $userDayPlan;
    }*/

    public function getUserWorkTimeByGroup($userId, $groupId,  $year, $week)
    {
        $dateWeekStart = new DateTime($year . 'W' . sprintf("%02d", $week));
        $weekUserPlan = new Application_Model_Db_User_Planning();
        $date = $dateWeekStart->format('Y-m-d');
        $workTime = $weekUserPlan->getTotalWorkTimeByGroup($userId, $groupId, $date);

        return $workTime;
    }

    public function saveDayAdditionalUserStatus($dayStatusData, $userId, $date, $groupId)
    {
        $saveStatus = true;
        foreach($dayStatusData as $statusId => &$status) {
            switch ($statusId) {
                case self::STATUS_DAY_WHITE:
                    break;
                case self::STATUS_DAY_GREEN:
                    //now no need edit day start/end in history
                    break;
                case self::STATUS_DAY_YELLOW:
                    break;
                case self::STATUS_DAY_RED:
                    $saveStatus = $this->_saveMissingData($saveStatus, self::STATUS_DAY_RED, $userId, $dayStatusData, $date);
                    break;
                case self::STATUS_DAY_CYAN:
                    $saveStatus = $this->_saveMissingData($saveStatus, self::STATUS_DAY_CYAN, $userId, $dayStatusData, $date);
                    break;
                case self::STATUS_DAY_BLUE:
                    $saveStatus = $this->_saveMissingData($saveStatus, self::STATUS_DAY_BLUE, $userId, $dayStatusData, $date);
                    break;
                case self::STATUS_DAY_OVERTIME:
                    $userOvertime = new Application_Model_Overtime();
                    $overtimeData = array(
                        'user_id'    => $userId,
                        'group_id'   => $groupId,
                        'date'       => $date,
                        'time_start' => $this->_getConcatTimeString($dayStatusData[$statusId]['time_start']),
                        'time_end'   => $this->_getConcatTimeString($dayStatusData[$statusId]['time_end'])
                    );
                    $saveStatusOvertime = $userOvertime->saveUserOvertimeDay($overtimeData);
                    $saveStatus = $saveStatus && $saveStatusOvertime;
                    break;
            }
        }
        return $saveStatus;
    }

    private function _saveMissingData($saveStatus, $statusId, $userId, $dayStatusData, $date)
    {
        $userMissing = new Application_Model_Missing();
        $missingDayData = array(
            "user_id"    => $userId,
            "status"     => $statusId,
            "time_start" => $this->_getConcatTimeString($dayStatusData[$statusId]['time_start']),
            "time_end"   => $this->_getConcatTimeString($dayStatusData[$statusId]['time_end']),
            "date"       => $date
        );
        $saveStatusMissing = $userMissing->saveUserMissingDay($missingDayData);
        return $saveStatus && $saveStatusMissing;
    }

    private function _getConcatTimeString($dayStatusTime)
    {
        if (isset($dayStatusTime['hour']) && $dayStatusTime['hour'] != "") {
            if (isset($dayStatusTime['min']) && $dayStatusTime['min'] != "") {
                 $statusTime = $dayStatusTime['hour'] . ":" . $dayStatusTime['min'] . ":00";
            } else {
                $statusTime = $dayStatusTime['hour'] . ":00:00";
            }
            return $statusTime;
        } else {
            return "";
        }
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
                        if (!empty($day['pause_start']) && !empty($day['pause_end'])) {
                            $day['format_pause_start'] =  My_DateTime::formatTime($day['pause_start']);
                            $day['format_pause_end']   =  My_DateTime::formatTime($day['pause_end']);
                        }
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
                    //Add overtime from manual entry data
                    $userOvertime = new Application_Model_Overtime();
                    $overtime = $userOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
                    if (!empty($overtime['time_start']) && !empty($overtime['time_end'])) {
                        $overtime    = array_merge($overtime, $this->_splitStartEndTimeString($overtime['time_start'], $overtime['time_end']));
                        $status = array_merge($overtime, $status);
                    }
                    //Add overtime from checkin
                    $workUserData = $this->_modelUser->getUserWorkData($userId, $date);
                    $status = array_merge(array('work_hours_overtime' => $workUserData['work_hours_overtime'] , 'work_time_overtime' => My_DateTime::TimeToDecimal($workUserData['work_hours_overtime'])), $status);
                    break;
            }
        }
        $statuses[self::STATUS_DAY_WHITE]['group_id'] = $groupId;
        $statuses[self::STATUS_DAY_WHITE]['user_id'] = $userId;
        $statuses[self::STATUS_DAY_WHITE]['date'] = $date;
        $statuses[self::STATUS_DAY_WHITE]['editable'] = $this->_getEditableDay($userId, $groupId);
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
            'format_time_start'   =>  My_DateTime::formatTime($timeStart),
            'format_time_end'   =>  My_DateTime::formatTime($timeEnd),

        );
    }

    public function getWeekHistory($userId, $groupId, $year, $week)
    {
        $workTime = $this->getUserWorkTimeByGroup($userId, $groupId, $year, $week);
        return $workTime;
    }
}