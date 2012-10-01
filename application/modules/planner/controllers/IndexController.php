<?php

class Planner_IndexController extends My_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->_forward('checking', null, null, array('action' => 'checking'));
    }

    public function checkingAction()
    {
        $modelUser = new Application_Model_User();
        $users = $modelUser->getAllUsers();
        $this->view->users = $users;
        $this->view->today = date('d.m.Y');
    }

}