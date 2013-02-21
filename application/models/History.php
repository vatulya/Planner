<?php

class Application_Model_History extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelPlanning   = new Application_Model_Planning();
        $this->_modelDbPlanning = new Application_Model_Db_User_Planning();
        $this->_modelMissing    = new Application_Model_Missing();
        $this->_modelOvertime   = new Application_Model_Overtime();
        $this->_modelRequest    = new Application_Model_Db_User_Requests();
        $this->_modelHistory    = new Application_Model_Db_User_History();
        $this->_modelChecks     = new Application_Model_Db_User_Checks();
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
        if ($dayPlan['status1'] === Application_Model_Planning::STATUS_DAY_GREEN && !empty($dayPlan['total_time'])) {
            if (!empty($approveUserDayRequest)) {
                $userHistoryData['vacation_time'] = $dayPlan['total_time'];
            } else {
                $userHistoryData['work_time'] = $dayPlan['total_time'];
            }
            if (!empty($missingUserDay['status'])) {
                $userHistoryData['missing_time'] = $missingUserDay['total_time'];
            }
        }

        $checks = $this->_modelChecks->getUserCheckTimeByIdDate($userId, $date);
        $fullDay = Application_Model_Day::factory($date, $userId);
        $fullUserDayPlan = $fullDay->getWorkPlanning();
        // Check overtime after work time
        if ( !empty($checks["check_out"]) && !empty($fullUserDayPlan['time_end']) &&  !empty($dayPlan['time_end'] ) &&
            $fullUserDayPlan['time_end'] == $dayPlan['time_end'] &&
            My_DateTime::compare($checks["check_out"], $fullUserDayPlan['time_end']) === 1) {
            $overtimeData = array(
                'user_id'    => $userId,
                'group_id'   => $groupId,
                'date'       => $date,
                'time_end2'   => $checks["check_out"]
            );
            if (My_DateTime::compare($checks["check_in"], $fullUserDayPlan['time_end']) === 1) {
                 $overtimeData['time_start2'] = $checks['check_in'];
            } else {
                 $overtimeData['time_start2'] = $fullUserDayPlan['time_end'];
            }
            $this->_modelOvertime->saveUserOvertimeDay($overtimeData);
        }
        $overtime = $this->_modelOvertime->getUserDayOvertimeByDate($userId, $groupId, $date);
        if (!empty($overtime)) {
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
}