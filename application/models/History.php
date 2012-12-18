<?php

class Application_Model_History extends Application_Model_Abstract
{
    protected $_modelDb;

    public function __construct()
    {
        $this->_modelPlanning = new Application_Model_Planning();
        $this->_modelDbPlanning = new Application_Model_Db_User_Planning();
        $this->_modelMissing  = new Application_Model_Missing();
        $this->_modelOvertime = new Application_Model_Db_User_Overtime();
        $this->_modelRequest  = new Application_Model_Db_User_Requests();
        $this->_modelHistory  = new Application_Model_Db_User_History();
    }

    public function addDayDataToHistory($userId, $groupId, $date)
    {
        $currentDate = new My_DateTime($date);
        $dateWeekYear = My_DateTime::getWeekYear($currentDate->getTimestamp());
        $dayPlan = $this->_modelPlanning->getUserDayPlanFromPlanning($userId, $groupId, $date);
        $missingUserDay = $this->_modelMissing->getUserDayMissingPlanByDate($userId, $date);
        $overtime = $this->_modelPlanning->getUserDayOvertimeByDate($userId, $groupId, $date);
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
        if (!empty($overtime)) {
            $userHistoryData['overtime_time'] =  $overtime['total_time'];
        }
        $this->_modelHistory->addUserWeekData($userHistoryData);
    }

    public function getUserWeekDataByWeekYear($userId, $groupId, $week, $year)
    {
        $history = $this->_modelHistory->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        //$keys = array(, "overtime_hours", "vacation_hours", "missing_hours");
        if (!empty($history)) {
            $history["work_hours"]     = My_DateTime::TimeToDecimal($history["work_time"]);
            $history["overtime_hours"] = My_DateTime::TimeToDecimal($history["overtime_time"]);
            $history["vacation_hours"] = My_DateTime::TimeToDecimal($history["vacation_time"]);
            $history["missing_hours"]  = My_DateTime::TimeToDecimal($history["missing_time"]);
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