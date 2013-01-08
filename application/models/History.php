<?php

class Application_Model_History extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelPlanning   = new Application_Model_Planning();
        $this->_modelDbPlanning = new Application_Model_Db_User_Planning();
        $this->_modelMissing    = new Application_Model_Missing();
        $this->_modelOvertime   = new Application_Model_Db_User_Overtime();
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
            $this->_modelPlanning->saveUserOvertimeDay($overtimeData);
        }
        $overtime = $this->_modelPlanning->getUserDayOvertimeByDate($userId, $groupId, $date);
        if (!empty($overtime)) {
            $userHistoryData['overtime_time'] =  $overtime['total_time'];
        }
        $this->_modelHistory->addUserWeekData($userHistoryData);
    }

    public function getUserWeekDataByWeekYear($userId, $groupId, $week, $year)
    {
        if ($groupId === false && $week === false) {
            $history = $this->_modelHistory->getUserHistoryDataByYear($userId, $year);
        } else {
            $history = $this->_modelHistory->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        }

        $userParameter = new Application_Model_Db_User_Parameters();
        $userParameters = $userParameter->getParametersByUserId($userId);
        //$keys = array(, "overtime_hours", "vacation_hours", "missing_hours");
        if (!empty($history)) {
            $history["work_hours"]     = My_DateTime::TimeToDecimal($history["work_time"]);
            $history["overtime_hours"] = My_DateTime::TimeToDecimal($history["overtime_time"]);
            $history["vacation_hours"] = My_DateTime::TimeToDecimal($history["vacation_time"]);
            $history["missing_hours"]  = My_DateTime::TimeToDecimal($history["missing_time"]);
            $history["free_hours"]     = My_DateTime::TimeToDecimal($userParameters['allowed_free_time']);
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