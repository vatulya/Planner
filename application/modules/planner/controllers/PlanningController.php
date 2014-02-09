<?php

class Planner_PlanningController extends My_Controller_Action
{
    const HISTORY_WEEK_NUM = 4;
    const NUM_OF_WORK_DAYS_ON_PAGE = 7;
    public $ajaxable = array(
        'index'             => array('html'),
        'get-week-details'  => array('json'),
        'get-edit-day-form' => array('html'),
        'save-day-form'     => array('json'),
        'get-more-history'  => array('json'),
    );

    protected $_modelGroup;
    protected $_modelUser;
    protected $_modelPlanning;

    public function init()
    {
        $this->_me      = $this->_helper->CurrentUser();
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        parent::init();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
        $this->_modelPlanning = new Application_Model_Planning();

        $this->view->weekDays     =  $weekDays = My_DateTime::getWeekDays();
        $this->view->currentWeekYear = My_DateTime::getWeekYear();
    }

    public function indexAction()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $week = $request->getParam('week');
        $year = $request->getParam('year');
        if (empty($week) || empty($year)) {
            $weekYear = My_DateTime::getWeekYear();
            $year  = $weekYear['year'];
            $week  = $weekYear['week'];
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
        $this->view->datesInterval = My_DateTime::getArrayOfDatesInterval(self::NUM_OF_WORK_DAYS_ON_PAGE, $week, $year);
        $this->view->week = $week;
        $this->view->year = $year;
        $this->view->numWorkDays = self::NUM_OF_WORK_DAYS_ON_PAGE;
        $this->view->historyDateWeekYear = $historyDateWeekYear;
        $this->view->groups              = $groups;
    }

    protected function _getUserData($user, $groupId, $year, $week)
    {
        $user['history'] = $this->_getHistory($user['id'], $groupId, $year, $week);
        $user['weekPlan'] = $this->_modelPlanning->getUserWeekPlanByGroup($user['id'], $groupId,  $year, $week, $this->_me['id']);
        return $user;
    }

    public function getEditDayFormAction()
    {
        $userId = $this->_getParam('user_id');
        $groupId = $this->_getParam('group_id');
        $date = $this->_getParam('date');
        $dayStatuses = $this->_modelPlanning->getUserDayStatuses($userId, $groupId, $date);

        $this->view->dayStatuses = $dayStatuses;
        $this->view->date        = $date;
        $this->view->userId      = $userId;
        $this->view->groupId     = $groupId;
        $this->_helper->layout->disableLayout();
    }

    public function saveDayFormAction()
    {
        $editForm = new Planner_Form_EditDay();
        /** @var $request Zend_Controller_Request_Http    */
        $request = $this->getRequest();
        $data = array();
        $status = false;
        $dayStatusData = $this->_getParam('day_status_data', array());
        $userId        = $this->_getParam('user_id');
        $date          = $this->_getParam('date');
        $groupId       = $this->_getParam('group_id');
        if (!empty($dayStatusData) && !empty($userId) && !empty($date) && !empty($groupId)) {
            $status = $this->_modelPlanning->saveDayAdditionalUserStatus($dayStatusData, $userId, $date, $groupId);
        } else {
            $data = $editForm->getErrors();
        }
        if ($status) {
            $this->_response(1, '', $data);
        } else {
            $this->_response(0, 'Error!', $data);
        }
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




}
