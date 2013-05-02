<?php

class Application_Model_History extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelPlanning   = new Application_Model_Planning();
        $this->_modelDbPlanning = new Application_Model_Db_User_Planning();
        $this->_modelMissing    = new Application_Model_Db_User_Missing();
        $this->_modelOvertime   = new Application_Model_Overtime();
        $this->_modelRequest    = new Application_Model_Db_User_Requests();
        $this->_modelHistory    = new Application_Model_Db_User_History();
        $this->_modelChecks     = new Application_Model_Db_User_Checks();
        $this->_modelUser       = new Application_Model_User();
        $this->_modelStatus     = new Application_Model_Status();
    }

    public function addDayDataToHistory($userId, $groupId, $date)
    {
        $currentDate = new My_DateTime($date);
        $dateWeekYear = My_DateTime::getWeekYear($currentDate->getTimestamp());
        $dayPlan = $this->_modelPlanning->getUserDayPlanFromPlanning($userId, $groupId, $date);
        $missingUserDay = $this->_modelMissing->getUserDayMissingPlanByDate($userId, $date);

        $approveUserDayRequest = $this->_modelRequest->getAllByUserId($userId, Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $date);
        $userHistoryData = array();
        $userHistoryData['user_id'] = $userId;
        $userHistoryData['group_id'] = $groupId;
        $userHistoryData['week'] = $dateWeekYear['week'];
        $userHistoryData['year'] = $dateWeekYear['year'];
        $userHistoryData['work_time'] = 0;
        $userHistoryData['overtime_time']  = 0;
        $userHistoryData['vacation_time']  = 0;
        $userHistoryData['missing_time']   = 0;
        $workData = $this->_modelUser->getUserWorkData($userId, $date);
        if ($dayPlan['status1'] === Application_Model_Planning::STATUS_DAY_GREEN && !empty($dayPlan['total_time']) && empty($approveUserDayRequest)) {
            if (!empty($missingUserDay) && is_array($missingUserDay)) {
                foreach($missingUserDay as $missing) {
                    $status = $this->_modelStatus->getDataById($missing['status']);
                    $missingTotalSeconds = Application_Model_Day::getWorkHoursByMarkers($missing['time_start'], $missing['time_end']);
                    if ($status['is_holiday']) {
                        $userHistoryData['vacation_time'] += $missingTotalSeconds;
                    } else {
                        $userHistoryData['missing_time'] += $missingTotalSeconds;
                    }
                }
            }
            if (!empty($approveUserDayRequest)) {
                $userHistoryData['vacation_time'] = $dayPlan['total_time'];
            } else {
                $userHistoryData['work_time'] = $workData['work_hours_done'];
                if ($userHistoryData['work_time'] < 0) {
                    $userHistoryData['work_time'] = 0;
                }
            }
        }
        //Calculate overtime by user's check In
        $lastUserWorkGroup =  $this->_modelDbPlanning->getGroupLastUserWork($userId, $date);
        if ($workData['work_hours_overtime'] > 0 && $groupId == $lastUserWorkGroup['group_id']) {
            $userHistoryData['overtime_time'] = $workData['work_hours_overtime'];
        }
        //Maybe somebody set on planning overtime without checkin so set it as overtime
        $overtime = $this->_modelOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
        if (!empty($overtime) && empty($userHistoryData['overtime_time'])) {
            $userHistoryData['overtime_time'] =  $overtime['total_time'];
        }
        $this->_modelHistory->addUserWeekData($userHistoryData);
    }

    public function getUserWeekDataByWeekYear($userId, $groupId, $week, $year)
    {
        //get summarize year data for user
        if ($groupId === false && $week === false) {
            $history = $this->_modelHistory->getUserHistoryDataByYear($userId, $year);
        } else {
        //get week data for user
            $history = $this->_modelHistory->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        }

        $history = $this->_replaceAllTimeFieldToDecimal($history, $year);
        return $history;
    }

    public function getAllUsersWeekDataByWeekYear($week, $year)
    {
        $allUsersHistory = array();
        $history = $this->_modelHistory->getUserWeekDataByWeekYear(false, false, $week, $year);
        $user = new Application_Model_User;
        $group = new Application_Model_Group;
        foreach ($history as $userGroupRow) {
            $userGroupRow = $this->_replaceAllTimeFieldToDecimal($userGroupRow, $year);
            $userGroupRow['user_id'] = $user->getUserById($userGroupRow['user_id']);
            $userGroupRow['group_id'] = $group->getGroupById($userGroupRow['group_id']);
            $allUsersHistory[] = $userGroupRow;
        }
        return $allUsersHistory;
    }

    private function _replaceAllTimeFieldToDecimal($history, $year)
    {
        if (!empty($history)) {
            $userParameter = new Application_Model_User();
            $userAllowedFreeTime = $userParameter->getAllowedFreeTime($history['user_id'], $year);
            $userAdditionalFreeTime = $userParameter->getAdditionalFreeTime($history['user_id'], $year);

            $history["work_hours"]       = My_DateTime::TimeToDecimal($history["work_time"]);
            $history["overtime_hours"]   = My_DateTime::TimeToDecimal($history["overtime_time"]);
            $history["vacation_hours"]   = My_DateTime::TimeToDecimal($history["vacation_time"]);
            $history["missing_hours"]    = My_DateTime::TimeToDecimal($history["missing_time"]);
            $history["free_hours"]       = My_DateTime::TimeToDecimal($userAllowedFreeTime);
            $history["additional_hours"] = My_DateTime::TimeToDecimal($userAdditionalFreeTime);
            $history["total"]  = My_DateTime::TimeToDecimal($history["work_time"] - $history["missing_time"] + $history["overtime_time"]);
        }
        return $history;
    }

    public function updateUserWeekData($userId, $groupId, $week, $year)
    {

        $this->_modelHistory->updateUserWeekData($userId, $groupId, $week, $year);
    }

    public function updateHistoryWeekHour($userId, $groupId, $field, $value, $year, $week)
    {
        //TODO check valid field
        $value = $value * 60 * 60;
        return $this->_modelHistory->updateHistoryWeekHour($userId, $groupId, $field, $value, $year, $week);
    }

    public function deleteUserWeekData($userId, $week, $year)
    {
        $this->_modelHistory->deleteUserWeekData($userId, $week, $year);
    }

    public function recalculateHistoryWeekForUser($userId, $date)
    {
         try {
            $currentDate = new My_DateTime($date);
            $weekYear = My_DateTime::getWeekYear($currentDate->getTimestamp());
            $year = $weekYear['year'];
            $week = $weekYear['week'];
        } catch (Exception $e) {
            $this->_response(0, 'Error!', 'Error create date.');
        }
        //delete data from history table for changed week
        $this->deleteUserWeekData($userId, $week, $year);
        //Add fresh data to history table for changed week
        $weekDays = My_DateTime::getWeekDays();
        $dateWeekStart = new My_DateTime($year . 'W' . sprintf("%02d", $week));
        $groupModel = new Application_Model_Group();
        $historyUserGroups = $groupModel->getAllGroupsFromHistory($year, $week, $userId);
        if (empty($historyUserGroups) || !is_array($historyUserGroups)) {
            return true;
        }
        foreach ($weekDays as $numDay=>$nameDay) {
            $date = clone $dateWeekStart;
            $date->modify('+' . $numDay . 'day');
            $dateFormat = $date->format('Y-m-d');
            try {
                $currentDate = new My_DateTime();
                $currentDateFormat = $currentDate->format('Y-m-d');
                $currentDate = new My_DateTime($currentDateFormat);
                $currentDate = $currentDate->getTimestamp();
                $date = new My_DateTime($dateFormat);
                //check future or past date
                if ($date->getTimestamp() >= $currentDate) {
                    return true;
                }  else {
                    foreach($historyUserGroups as $group) {
                        $this->addDayDataToHistory($userId, $group['id'], $dateFormat);
                    }
                }
            } catch (Exception $e) {
                return false;
            }


        }
        return true;
    }

    public function getAlertSummaryGroupWeekData($groupId, $week, $year)
    {
        $groupAlert = $this->_modelHistory->getIncidentGroupWeekData($groupId, $week, $year);
        return $groupAlert;
    }
}