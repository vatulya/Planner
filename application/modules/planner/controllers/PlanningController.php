<?php

class Planner_PlanningController extends My_Controller_Action
{
    const HISTORY_WEEK_NUM = 4;
    public $ajaxable = array(
        'index'             => array('html'),
        'get-week-details'  => array('json'),
        'get-edit-day-form' => array('html'),
        'save-day-form'     => array('json'),
        'get-more-history'  => array('json'),
    );

    protected $_modelGroup;
    protected $_modelUser;

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
        if (empty($week) || empty($year))  {
            $weekYear = My_DateTime::getWeekYear();
            $year = $weekYear['year'];
            $week = $weekYear['week'];
        }
        $this->view->nextWeekYear = My_DateTime::getNextYearWeek($year,$week);
        $this->view->prevWeekYear = My_DateTime::getPrevYearWeek($year,$week);

        $historyDateWeekYear = My_DateTime::getNumHistoryWeeks($year, $week);
        $groups = $this->_modelGroup->getAllGroups();

        foreach ($groups as $key => $group) {
            $groupId = $group['id'];
            $groups[$key] = $group;
            $groups[$key]['users'] = $this->_modelUser->getAllUsersByGroup($groupId);
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
        $user['history'] = $this->_getHistory($user['user_id'], $groupId, $year, $week);
        $user['weekPlan'] = $this->_modelPlanning->getUserWeekPlanByGroup($user['user_id'], $groupId,  $year, $week, $this->_me['id']);
        return $user;
    }

    public function getEditDayFormAction()
    {
        $userId = $this->_getParam('user_id');
        $groupId = $this->_getParam('group_id');
        $date = $this->_getParam('date');

        $editForm = new Planner_Form_EditDay(array(
            'class' => 'edit-day-form',
            'action' => $this->_helper->url->url(array('controller' => 'planning', 'action' => 'save-day-form'), 'planner', true),
            'id' => 'form-edit-day',
        ));
        $day = $this->_modelPlanning->getDayGroupUserPlanByDate($userId, $groupId, $date, $this->_me['id']);
        if (!empty($day['status1']['id'])) {
            $day['status1'] = $day['status1']['id'];
        }
        if (!empty($day['status2']['id'])) {
            $day['status2'] = $day['status2']['id'];
        }
        $editForm->populate($day);
        $this->view->day = $day;
        $this->_helper->layout->disableLayout();
        $this->view->editForm = $editForm->prepareDecorators();
    }

    public function saveDayFormAction()
    {
        $editForm = new Planner_Form_EditDay();
        /** @var $request Zend_Controller_Request_Http    */
        $request = $this->getRequest();
        $data = array();
        $status = false;
        if ($request->isPost()) {
            if ($editForm->isValid($request->getPost())) {
                $status = $this->_modelPlanning->saveDayAdditionalUserStatus($editForm->getValues());
            } else {
                $data = $editForm->getErrors();
            }
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
        $historyWeeks = My_DateTime::getNumHistoryWeeks($fromYear, $fromWeek, $weeksCount);
        foreach ($historyWeeks as $week => $year) {
            $history[$week] = $this->_getWeekHistory($userId, $groupId, $year, $week);
        }
        //$history = array_reverse($history, true);
        return $history;
    }

    protected function _getWeekHistory($userId, $groupId, $year, $week)
    {
        return $this->_modelPlanning->getUserWorkTimeByGroup($userId, $groupId, $year, $week);
    }


}
