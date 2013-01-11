<?php

class Application_Model_Overview extends Application_Model_Abstract
{
    const HISTORY_WEEK_NUM = 4;

    protected $_modelUser;
    protected $_modelGroup;
    protected $_modelHistory;
    protected $_modelPlanning;

    public function __construct()
    {
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
        $this->_modelPlanning = new Application_Model_Planning();
        $this->_modelHistory  = new Application_Model_History();
        if (!defined('EXPORT_PATH_DIR')) {
            define('EXPORT_PATH_DIR' , APPLICATION_PATH . '/../public/export');
        }
    }

    public function getAllGroupAndUserWeekSummary($week, $year)
    {
        $groups = $this->_modelGroup->getAllGroupsFromHistory($year, $week);

        foreach ($groups as $key => $group) {
            $groupId = $group['id'];
            $groups[$key] = $group;
            $groups[$key]['users'] = $this->_modelUser->getAllUsersFromHistory($groupId, $year, $week);
            if (!empty($groups[$key]['users'])) {
                foreach ($groups[$key]['users'] as $keyUser => $user) {
                    $user = $this->_getUserData($user, $groupId, $year, $week);
                    $groups[$key]['users'][$keyUser] = $user;
                }
            }
        }

        return $groups;
    }

    protected function _getUserData($user, $groupId, $year, $week)
    {
        $user['history'] = $this->_getHistory($user['id'], $groupId, $year, $week);
        $modelHistory = new Application_Model_History();
        $user['weekHours'] = $modelHistory->getUserWeekDataByWeekYear($user['id'], $groupId, $week, $year);
        return $user;
    }

    protected function _getHistory($userId, $groupId, $fromYear, $fromWeek, $weeksCount = self::HISTORY_WEEK_NUM)
    {
        $history = array();
        $modelHistory = new Application_Model_History();
        $historyWeeks = My_DateTime::getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount);
        foreach ($historyWeeks as $week => $year) {
            $history[$week] = $modelHistory->getUserWeekDataByWeekYear($userId, $groupId, $week, $year);
        }
        return $history;
    }

    public function writeFileCSV($arrayAllData, $fullPath) {
        $delimiter = ";";
        $enclosure = '"';
        $file = fopen( $fullPath, 'w' );
        $headerData = array("Group", "Name", "Gewerkte uren", "Over uren", "Vrije uren", "Ziekte/buitengew", "Open uren", "Total uren" );
        fputcsv($file, $headerData, $delimiter, $enclosure);
        foreach ( $arrayAllData as $rowData ) {
            foreach ($rowData['users'] as $user) {
                $userData = array(
                    $rowData['group_name'],
                    $user['full_name'],
                    $user['weekHours']['work_hours'],
                    $user['weekHours']['overtime_hours'],
                    $user['weekHours']['vacation_hours'],
                    $user['weekHours']['missing_hours'],
                    $user['weekHours']['free_hours'],
                    $user['weekHours']['total'],
                );
                fputcsv($file, $userData, $delimiter, $enclosure);
            }

        }
        @fclose($file);
        return true;
    }

    public function getHistoryDataFile($week, $year)
    {
        $groupsUserData = $this->getAllGroupAndUserWeekSummary($week, $year);
        $filename = 'week_' . $week . '_year_' . $year .'_history_data_export.csv';
        $fullPath = EXPORT_PATH_DIR . '/' . $filename;
        $this->writeFileCSV($groupsUserData, $fullPath);
        return $filename;
    }
}