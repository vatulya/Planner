<?php

class Planner_OverviewController extends My_Controller_Action
{
    const HISTORY_WEEK_NUM = 4;

    public $ajaxable = array(
        'index'     => array('html'),
        'update-history-hour' => array('json'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;
    protected $_modelGroup;
    protected $_modelHistory;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();
        $this->_modelHistory = new Application_Model_History();

        if ( ! $this->_getParam('user')) {
            $this->_setParam('user', $this->_me['id']);
        } else {
            $allowed = false;
            if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
                $allowed = true;
            }
            if ( ! $allowed) {
                $this->_setParam('user', $this->_me['id']);
            }
        }
        $this->_me      = $this->_helper->CurrentUser();
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
        $this->_modelPlanning = new Application_Model_Planning();

        $this->view->weekDays     =  $weekDays = My_DateTime::getWeekDays();
        $this->view->currentWeekYear = My_DateTime::getWeekYear();
        parent::init();
    }

    public function indexAction()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $week = $request->getParam('week');
        $year = $request->getParam('year');
        if (empty($week) || empty($year))  {
            $weekYear = My_DateTime::getWeekYear();
            $year = $weekYear['year'];
            $week = $weekYear['week'];
        }
        $this->view->nextWeekYear = My_DateTime::getNextYearWeek($year,$week);
        $this->view->prevWeekYear = My_DateTime::getPrevYearWeek($year,$week);

        $historyDateWeekYear = My_DateTime::getNumHistoryWeeks($year, $week);
        $groups = $this->_modelGroup->getAllGroupsFromHistory($year, $week);
        //$groups = $this->_modelGroup->getAllGroups();

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
        $this->view->week                = $week;
        $this->view->year                = $year;
        $this->view->historyDateWeekYear = $historyDateWeekYear;
        $this->view->groups              = $groups;
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


    public function updateHistoryHourAction()
    {
        $status  = false;
        if ($this->_me['role'] == Application_Model_Auth::ROLE_SUPER_ADMIN) {
            $field  = $this->_getParam('field');
            $value  = $this->_getParam('value');
            $userId  = $this->_getParam('user');
            $groupId  = $this->_getParam('group');
            $year  = $this->_getParam('year');
            $week  = $this->_getParam('week');
            if ($value > 0) {
                $status = $this->_modelHistory->updateHistoryWeekHour($userId, $groupId, $field, $value, $year, $week);
            }
        }
        if ($status) {
            $this->_response(1, '', array());
        } else {
            $this->_response(0, 'Error!', "No valid time");
        }
    }

}