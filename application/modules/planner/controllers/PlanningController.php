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

    protected $_modelUser;

    protected $_me;

    protected $_project;

    public function init()
    {
        parent::init();
        $this->_modelGroup    = new Application_Model_Group();
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
            $users = $this->_modelUser->getAllUsersByGroup();
            $user = $this->_getUserData($user, $year, $week);
            $users[$key] = $user;
        }
        $this->view->week  = $week;
        $this->view->users = $users;
    }

    protected function _getUserData($user, $year, $week)
    {
        return $user;
    }

}