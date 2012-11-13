<?php

class Planner_UserSettingsController extends My_Controller_Action
{

    public $ajaxable = array(
        'index' => array('html'),
    );

    /**
     * @var Application_Model_User
     */
    protected $_modelUser;

    public function init()
    {
        $this->view->me = $this->_me = $this->_helper->CurrentUser();
        $this->_modelUser = new Application_Model_User();

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

        parent::init();
    }

    public function indexAction()
    {
        $modelGroup = new Application_Model_Group();
        $users = $this->_modelUser->getAllUsers();
        foreach ($users as $key => $user) {
            $user['groups'] = $modelGroup->getGroupsByUserId($user['id']);
//            $user['hours'];
//            $user['time_work']['start']; $user['time_work']['end'];
            $users[$key] = $user;
        }
        $this->view->users = $users;
    }

}