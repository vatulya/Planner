<?php

class Application_Model_Alert extends Application_Model_Abstract
{
    protected $_modelDb;
    protected $_modelGroup;
    protected $_modelHistory;

    public function __construct()
    {
        $this->_modelDb         = new Application_Model_Db_User_Alerts();
        $this->_modelGroup      = new Application_Model_Group();
        $this->_modelHistory    = new Application_Model_History();
        $this->_modelDbHistory  = new Application_Model_Db_User_History();
    }

    public function getAllGroupWeekAlertSummary($week, $year)
    {
        $groups = $this->_modelGroup->getAllGroupsFromHistory($year, $week);
        $groupAlerts = $this->_getAlertsGroupByWeek($year, $week);
        foreach ($groups as $key => $group) {
            $groupId = $group['id'];
            $groups[$key] = $group;
            $groups[$key]['history'] = $this->_getAlertSummaryHistory($groupId, $year, $week);
            if (!empty($groupAlerts[$groupId])) {
                $groups[$key]['week_group_alerts'] = $groupAlerts[$groupId];
            }
        }
        return $groups;
    }

    protected function _getAlertSummaryHistory($groupId, $fromYear, $fromWeek, $weeksCount = Application_Model_Overview::HISTORY_WEEK_NUM)
    {
        $history = array();
        $historyWeeks = My_DateTime::getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount);
        foreach ($historyWeeks as $week => $year) {
            $history[$week] = $this->_modelHistory->getAlertSummaryGroupWeekData($groupId, $week, $year);
        }
        return $history;
    }

    protected function _getAlertsGroupByWeek($year, $week)
    {
        $groupNormalizedAlerts = array();
        $groupAlerts = $this->_modelDbHistory->getGroupAlertsByWeek($week, $year);
        if (!empty($groupAlerts)) {
            foreach ($groupAlerts as $groupAlert) {
                $groupNormalizedAlerts[$groupAlert['group_id']] = $groupAlert['summ_incidents'];
            }
        }
        return $groupNormalizedAlerts;
    }

    public function getUserAlertsByDate($userId, $groupId, $date)
    {

    }

    public function getUserAlertsByDateInterval($userId, $groupId, $dateStart, $dateEnd)
    {

    }

    public function getCountUserAlertsByWeek($userId, $groupId, $week, $year)
    {
        return count($this->getUserAlertsByWeek($userId, $groupId, $week, $year));
    }

    public function getUserAlertsByYear($year)
    {
        //return $this->_modelDb->getUserAlertsByYear($year);

        $yearUsersAlerts = array();
        //Get data from prepared table user_history
        $groups = $this->_modelGroup->getAllGroups();
        foreach ($groups as $groupId => $group) {
            $userAlerts = $this->_modelDbHistory->getUserAlertsByGroupYear($groupId, $year);
            if (!empty($userAlerts)) {
                foreach ($userAlerts as $userAlert) {
                    $yearUsersAlerts[$groupId][$userAlert['user_id']] = $userAlert;
                }
            }
        }
        return $yearUsersAlerts;
    }

    public function getGroupAlertsByYear($year)
    {
        $yearGroupAlerts = array();
        //Get data from prepared table user_history
            $alerts = $this->_modelDbHistory->getGroupAlertsByYear($year);
            if (!empty($alerts)) {
                foreach ($alerts as $alert) {
                    $yearGroupAlerts[$alert['group_id']] = $alert;
                }
            }
        return $yearGroupAlerts;
    }

    public function getUserAlertsByWeek($userId, $groupId, $week, $year)
    {
        $date = My_DateTime::getDateIntervalByWeekYear($year, $week);
        $alerts = $this->_modelDb->getUserAlertsByDateInterval($userId, $groupId, $date['start'], $date['end']);
        return $alerts;
    }

    public function checkAlertDayUserPlanByGroup($userId, $groupId, $date)
    {
        //Check alerts
        $planning = new Application_Model_Planning();
        $userGroupStatuses = $planning->getUserDayStatuses($userId, $groupId, $date);

        foreach($userGroupStatuses as $status) {
            $alertExist = false;
            switch ($status['id']) {
                case Application_Model_Planning::STATUS_DAY_GREEN:
                    $userCheck = new Application_Model_Db_User_Checks();
                    $userCheckIn = $userCheck->checkUserIn($userId);
                    if (isset($status['time_start']) && $status['time_end']
                        && My_DateTime::compare(new My_DateTime(), $status['time_start']) === 1
                        && My_DateTime::compare($status['time_end'], new My_DateTime()) === 1
                        && !$userCheckIn
                    ) {
                        //User have missing status at the moment
                        $noMissingStatuses = true;
                        //From green to blue statuses (1-4 id)
                        for($i = 4; $i < 7; $i++) {
                            //Missing status interval crossing with work time
                            if (
                                isset($userGroupStatuses[$i]['time_start']) && $userGroupStatuses[$i]['time_end']
                                && (
                                       My_DateTime::compare($userGroupStatuses[$i]['time_start'], $status['time_start']) === 1
                                    && My_DateTime::compare($status['time_end'], $userGroupStatuses[$i]['time_start']) === 1
                                    || My_DateTime::compare($userGroupStatuses[$i]['time_end'], $status['time_start']) === 1
                                    && My_DateTime::compare($status['time_end'], $userGroupStatuses[$i]['time_end']) === 1
                                    || My_DateTime::compare($status['time_start'], $userGroupStatuses[$i]['time_start']) === 1
                                    && My_DateTime::compare($userGroupStatuses[$i]['time_end'], $status['time_end']) === 1
                                )
                                || isset($userGroupStatuses[Application_Model_Planning::STATUS_DAY_YELLOW]['status']) && $userGroupStatuses[Application_Model_Planning::STATUS_DAY_YELLOW]['status'] == 'approved'
                            ) {
                                $noMissingStatuses = false;
                            }
                        }
                        if ($noMissingStatuses) {
                            $alertExist = 1;
                        }
                    }
                    break;
                case Application_Model_Planning::STATUS_DAY_YELLOW:
                    if (isset($status['status']) && $status['status'] == 'approved') {
                        $alertExist =  1;
                    }
                    break;
                case Application_Model_Planning::STATUS_DAY_RED:
                    if ($this->_checkCrossTimeInterval($userGroupStatuses, $status['id'])) {
                        $alertExist = 1;
                    }
                    break;
                case Application_Model_Planning::STATUS_DAY_CYAN:
                    if ($this->_checkCrossTimeInterval($userGroupStatuses, $status['id'])) {
                        $alertExist = 1;
                    }
                    break;
                case Application_Model_Planning::STATUS_DAY_BLUE:
                    if ($this->_checkCrossTimeInterval($userGroupStatuses, $status['id'])) {
                        $alertExist = 1;
                    }
                    break;
                case Application_Model_Planning::STATUS_DAY_OVERTIME:
                    //work over planning
                    if (isset($userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_start'])
                        && isset($userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_end'])
                    ) {
                        $timeStart = $userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_start'];
                        $timeEnd   = $userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_end'];
                        if (
                               isset($status['time_start']) && $status['time_end']
                            && My_DateTime::compare($status['time_start'], $timeStart) === 1
                            && My_DateTime::compare($status['time_end'], $timeStart) === 1
                            && My_DateTime::compare($status['time_start'], $timeEnd) === 1
                            && My_DateTime::compare($status['time_end'], $timeEnd) === 1
                        ) {
                            $alertExist = 1;
                        }
                    }
                    break;

            }
            if ($alertExist) {
                $this->saveAlerts($userId, $groupId, $date, $status['id']);
                $this->sendAlertEmail($userId, $groupId, $date);
            }
        }
        return true;
    }

    public  function sendAlertEmail($userId, $groupId, $date)
    {
        $userModel = new Application_Model_User();
        $user = $userModel->getUserById($userId);
        $group = $this->_modelGroup->getGroupById($groupId);
        $dbMail = new Application_Model_Db_User_Mail();
        $mailTo = $dbMail->getMailArray('alerts');
        $alert = $this->_modelDb->getNoSendedAlerts($userId, $groupId, $date);
        if (!empty($alert) && !empty($mailTo)) {
            $mail = new Zend_Mail();
            $mailBody = 'User : ' . $user['full_name']
                . '. From group : ' . $group['group_name']
                . '. Have alert : ' . $alert['alert_description']
            ;
            $mail->setBodyText($mailBody);
            $mail->setFrom('alerts@planner.futurumshop.com');
            $mail->setSubject('Planning Alert');
            $mail->addTo($mailTo);
            $mail->send();
            //Set all alerts are sended
            $this->_modelDb->updateAlertsNoticedMailed($userId, $groupId, $date);
        }
    }

    private function _checkCrossTimeInterval($userGroupStatuses, $status)
    {
        if (isset($userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_start'])
            && isset($userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_end'])
        ) {
            $timeStart = $userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_start'];
            $timeEnd   = $userGroupStatuses[Application_Model_Planning::STATUS_DAY_GREEN]['time_end'];
            if (
                isset($userGroupStatuses[$status]['time_start']) && $userGroupStatuses[$status]['time_end']
                && (
                    My_DateTime::compare($userGroupStatuses[$status]['time_start'], $timeStart) === 1
                    && My_DateTime::compare($timeEnd, $userGroupStatuses[$status]['time_start']) === 1
                    || My_DateTime::compare($userGroupStatuses[$status]['time_end'], $timeStart) === 1
                    && My_DateTime::compare($timeEnd, $userGroupStatuses[$status]['time_end']) === 1
                )
            ) {
                return true;
            }
        }
        return false;
    }

    public function saveAlerts($userId, $groupId, $date, $type)
    {
        $this->_modelDb->saveAlert($date, $type, $userId, $groupId);
        $weekYear = My_DateTime::getWeekYearByDate($date);
        $this->_modelDbHistory->updateNumIncidentInHistory($userId, $groupId, $weekYear['year'], $weekYear['week']);
    }

    public function getCountAlertsByType($date, $type)
    {
        return $this->_modelDb->getCountAlertsByType($date, $type);
    }

    public function getNumToManyPeopleOff($date)
    {
        $generalGroupSettings = $this->_modelGroup->getGeneralGroupSettings();
        $numAlerts = $this->_modelDb->getCountAlertsByType($date);
        $numPeopleOff =  $numAlerts - $generalGroupSettings['max_free_people'] - $generalGroupSettings['alert_over_limit_free_people'];
        if ($numPeopleOff > 0) {
            return $numPeopleOff;
        }
        return 0;
    }
}