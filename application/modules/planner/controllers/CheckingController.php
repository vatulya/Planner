<?php

class Planner_CheckingController extends My_Controller_Action
{

    public $ajaxable = array(
        'index'      => array('html'),
        'user-check' => array('json'),
    );

    protected $_modelUser;

    public function init()
    {
        $this->_modelUser = new Application_Model_User();
        $group = $this->_getParam('group');
        $allowed = false;
        if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
            $allowed = true;
        } elseif ($group && in_array($group, $this->_me['admin_groups'])) {
            $allowed = true;
        }
        if ( ! $allowed) {
            $this->_setParam('user', $this->_me['id']);
        }
        parent::init();
    }

    public function indexAction()
    {
        $date = new DateTime();
        $users = $this->_modelUser->getAllUsers($date);
        $this->view->users = $users;
        $this->view->date = $date->format('d.m.Y');
    }

    public function userCheckAction()
    {
        $user = $this->_modelUser->userCheck($this->_getParam('user'), $check = $this->_getParam('check'));
        if ($user) {
            $this->_response(1, '', $user);
        } else {
            $this->_response(0, 'Error!', array());
        }
    }

}