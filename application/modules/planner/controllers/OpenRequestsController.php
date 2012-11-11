<?php

class Planner_OpenRequestsController extends My_Controller_Action
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
        $modelRequest = new Application_Model_Request();
        $groups = array();
        if ($this->_me['role'] >= Application_Model_Auth::ROLE_ADMIN) {
            $groups = $modelGroup->getAllGroups();
        } else { // group Admin
            foreach ($this->_me['admin_groups'] as $groupId) {
                $groups[] = $modelGroup->getGroupById($groupId);
            }
        }
        foreach ($groups as $key => $group) {
            $users = $this->_modelUser->getAllUsersByGroup($group['id']);
            $group['users'] = $users;
            foreach ($group['users'] as $key => $user) {
                $user['requests'] = $modelRequest->getRequestsByUserId($user['id']);
                $group['users'][$key] = $user;
            }
            $groups[$key] = $group;
        }

//        $requestDetailsForm = new Planner_Form_RequestDetails();

//        $this->view->requestDetailsForm = $requestDetailsForm;
        $this->view->groups = $groups;
    }

}