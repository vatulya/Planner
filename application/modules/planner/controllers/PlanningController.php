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
        $this->_me           = $this->_helper->CurrentUser();
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        parent::init();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
        $this->_modelPlanning = new Application_Model_Planning();

    //    $currentWeekDateMonday = new DateTime();
       // $currentWeekDateMonday->modify('last Monday');  2012-10-05
       // $this->view->currentWeekDateMonday  = $currentWeekDateMonday->format('W');
       // $this->view->nextWeekDateMonday     = $currentWeekDateMonday->format('W');

     //     $currentWeekDate = new DateTime();
    //    $this->view->nextWeekYear = $nextWeekDate->format('Y');
    //    $this->view->nextWeek     = $nextWeekDate->format('W');
//        $this->view->nextWeekYear = date('Y', strtotime('Monday this week +1 week'));
//        $this->view->nextWeek     = date('W', strtotime('Monday this week +1 week'));
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
          //  echo "<pre>";
          //  var_dump($groups[$key]['users']);
        }
        $this->view->week                = $week;
        $this->view->year                = $year;
        $this->view->historyDateWeekYear = $historyDateWeekYear;
        $this->view->groups              = $groups;
    }

    protected function _getUserData($user, $groupId, $year, $week)
    {

        $user['history'] = $this->_getHistory($user['user_id'], $groupId, $year, $week);
        //echo "<pre>";
        //var_dump($user['history']);

        $user['weekPlan'] = $this->_modelPlanning->getUserWeekPlanByGroup($user['user_id'], $groupId,  $year, $week);
        return $user;
    }

    public function getEditDayFormAction()
    {
        $dayId = $this->_getParam('day');
        $editForm = new Planner_Form_EditDay(array(
            'class' => 'edit-day-form',
            'action' => $this->_helper->url->url(array('controller' => 'planning', 'action' => 'save-day-form'), 'planner', true),
            'id' => 'form-edit-day',
        ));

        if ($dayId) {
            $day = $this->_modelPlanning->getUserDayById($dayId);
            if ($day) {
                $missingModel = new Application_Model_Missing();
                $overtime = $this->_modelPlanning->getUserDayOvertimeByDate($day['user_id'], $day['group_id'], $day['date']);
                $missingDayStatus = $missingModel->getUserDayMissingPlanByDate($day['user_id'], $day['date']);
                $formData = array(
                    "id"          => $day['id'],
                    "status_main" => $day['status1'],
                    "time_start"  => $day['time_start'],
                    "time_end"    => $day['time_end'],
                );
                if ($overtime) {
                    $formData['time_start2'] =  $overtime['time_start'];
                    $formData['time_end2']   =  $overtime['time_end'];
                    $formData['status']      =  Application_Model_Planning::STATUS_DAY_OVERTIME;
                } elseif (!empty($missingDayStatus['status'])) {
                    $formData['time_start2'] =  $missingDayStatus['time_start'];
                    $formData['time_end2']   =  $missingDayStatus['time_end'];
                    $formData['status']      =  $missingDayStatus['status'];
                }
                $editForm->populate($formData);
            } else {
                return false;
            }
            $this->view->day = $day;
        }
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

    public function getMoreHistoryAction()
    {
        $oldestYear = $this->_getParam('oldestYear');
        $oldestWeek = $this->_getParam('oldestWeek');

        $user['history'] = $this->_getHistory($userId, $oldestYear, $oldestWeek);
        $html = $view->render('_blocks/history.phtml');
        $data = array(
            'html' => $html,
        );
        $this->_response(1, '', $data);
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
