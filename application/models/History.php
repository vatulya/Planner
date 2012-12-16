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
        if (empty($dayPlan['status1'])) {
            // return false;
        }
        $missingUserDay = $this->_modelMissing->getUserDayMissingPlanByDate($userId, $date);
        $overtime = $this->_modelPlanning->getUserDayOvertimeByDate($userId, $groupId, $date);
        $approveUserDayRequest = $this->_modelRequest->getAllByUserId($userId, Application_Model_Db_User_Requests::USER_REQUEST_STATUS_APPROVED, $date);
        $userHistoryData = array();
        $userHistoryData['user_id'] = $userId;
        $userHistoryData['group_id'] = $groupId;
        $userHistoryData['week'] = $dateWeekYear['week'];
        $userHistoryData['year'] = $dateWeekYear['year'];
        $userHistoryData['work_hours'] = 0;
        $userHistoryData['overtime_hours']  = 0;
        $userHistoryData['vacation_hours']  = 0;
        $userHistoryData['missing_hours']   = 0;
        if ($dayPlan['status1'] === Application_Model_Planning::STATUS_DAY_GREEN && !empty($dayPlan['total_time'])) {
            if (!empty($approveUserDayRequest)) {
                $userHistoryData['vacation_hours'] = $dayPlan['total_time'];
            } else {
                $userHistoryData['work_hours'] = $dayPlan['total_time'];
            }
            if (!empty($missingUserDay['status'])) {
                $userHistoryData['missing_hours'] = $missingUserDay['total_time'];
            }
        }
        if (!empty($overtime)) {
            $userHistoryData['overtime_hours'] =  $overtime['total_time'];
        }
        $this->_modelHistory->addUserWeekData($userHistoryData);

    }

    public function getUserWeekDataByWeekYear($userId, $groupId, $week, $year)
    {
        $history = $this->_modelHistory->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        //$keys = array(, "overtime_hours", "vacation_hours", "missing_hours");
        if (!empty($history)) {
            $history["work_hours"]     = $history["work_hours"];
            $history["overtime_hours"] = $history["overtime_hours"];
            $history["vacation_hours"] = $history["vacation_hours"];
            $history["missing_hours"]  = $history["missing_hours"];
            $history["total"]  = $history["work_hours"] - $history["missing_hours"] + $history["overtime_hours"];
        }
        return $history;
    }
}