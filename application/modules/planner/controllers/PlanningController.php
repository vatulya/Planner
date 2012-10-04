<?php

class Planner_PlanningController extends My_Controller_Action
{

    public $ajaxable = array(
        'get-week-details' => array('json'),
        'get-user'         => array('json'),
        'get-user-task'    => array('json'),
        'toggle-task'      => array('json'),
        'get-more-history' => array('json'),
        'repair-week'      => array('json'),
    );

    protected $_modelGroup;
    protected $_modelUser;

    public function init()
    {
        parent::init();
        $this->_modelGroup    = new Application_Model_Group();
        $this->_modelUser     = new Application_Model_User();
    //    $this->_me           = $this->_helper->CurrentUser();
    //    $this->view->me      = $this->_helper->CurrentUser();
    //    $this->_setParam('userId', $this->_me['id']);


        $nextWeekDate = new DateTime();
        $nextWeekDate->modify('+1 week');
        $this->view->nextWeekYear = $nextWeekDate->format('Y');
        $this->view->nextWeek     = $nextWeekDate->format('W');
//        $this->view->nextWeekYear = date('Y', strtotime('Monday this week +1 week'));
//        $this->view->nextWeek     = date('W', strtotime('Monday this week +1 week'));

    }

    public function indexAction()
    {
       // $users = array($this->_me);
        $year = date('Y');
        $week = date('W');
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

        $this->view->week  = $week;
        $this->view->groups = $groups;
    }

    protected function _getUserData($user, $groupId, $year, $week)
    {
        //$user['history'] = $this->_modelUser->_getHistory($user, $groupId,$year, $week);
        $user['week_plan'] = $this->_modelUser->getUserWeekPlanByGroup($user, $groupId,  $year, $week);
        $user['week_plan']['monday'] = "info";
        $user['week_plan']['tuesday'] = "info";
        $user['week_plan']['wednesday'] = "info";
        $user['week_plan']['thursday'] = "info";
        $user['week_plan']['friday'] = "info";
        $user['week_plan']['saturday'] = "info";
        return $user;
    }

}